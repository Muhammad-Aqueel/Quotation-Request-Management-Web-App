-- Users
CREATE TABLE  IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) UNIQUE,
  password_hash VARCHAR(255),
  email VARCHAR(100),
  role ENUM('admin', 'student', 'osas') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create categories table
CREATE TABLE IF NOT EXISTS request_categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create societies table
CREATE TABLE IF NOT EXISTS societies (
  id INT AUTO_INCREMENT PRIMARY KEY,
  society_name VARCHAR(255) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Request Table
CREATE TABLE IF NOT EXISTS requests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255),
  category_id int,
  user_id int,
  society_id INT,
  description TEXT,
  event_date VARCHAR(100),
  status ENUM('0','1') NOT NULL,
  approval_status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
  purchase_order ENUM('0','1') NOT NULL DEFAULT '0',
  po_gt DECIMAL(10,2),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES request_categories(id),
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (society_id) REFERENCES societies(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Items in each request
CREATE TABLE IF NOT EXISTS request_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  request_id INT,
  item_name VARCHAR(255),
  quantity INT,
  FOREIGN KEY (request_id) REFERENCES requests(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Request Attachments
CREATE TABLE IF NOT EXISTS request_attachments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  request_id INT,
  filename VARCHAR(255),
  filepath TEXT,
  FOREIGN KEY (request_id) REFERENCES requests(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Vendor Table
CREATE TABLE IF NOT EXISTS vendors (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255),
  email VARCHAR(100),
  phone VARCHAR(50),
  company VARCHAR(255),
  ntn VARCHAR(255),
  submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Quotations Table
CREATE TABLE IF NOT EXISTS quotations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  vendor_id INT,
  request_id INT,
  total_amount DECIMAL(10,2),
  message TEXT,
  submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  status ENUM('Pending', 'Approved', 'Rejected', 'Deleted') DEFAULT 'Pending',
  is_read TINYINT(1) DEFAULT 0,
  FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE CASCADE,
  FOREIGN KEY (request_id) REFERENCES requests(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Quotation Items
CREATE TABLE IF NOT EXISTS quotation_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  quotation_id INT,
  request_item_id INT,
  unit_price DECIMAL(10,2),
  FOREIGN KEY (quotation_id) REFERENCES quotations(id) ON DELETE CASCADE,
  FOREIGN KEY (request_item_id) REFERENCES request_items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Quotation Attachments
CREATE TABLE IF NOT EXISTS quotation_attachments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  quotation_id INT,
  filename VARCHAR(255),
  filepath TEXT,
  FOREIGN KEY (quotation_id) REFERENCES quotations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- terms_and_conditions
CREATE TABLE IF NOT EXISTS terms_and_conditions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  content TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `terms_and_conditions` (`content`) SELECT 'Terms and conditions...' WHERE NOT EXISTS ( SELECT 1 FROM `terms_and_conditions` );

-- Request_items trigger after quantity changes
CREATE TRIGGER IF NOT EXISTS trg_update_total_on_quantity_change
AFTER UPDATE ON request_items
FOR EACH ROW
BEGIN
  DECLARE done INT DEFAULT FALSE;
  DECLARE qid INT;

  -- Cursor to loop over all quotation_ids that use the updated request_item_id
  DECLARE cur CURSOR FOR
    SELECT quotation_id FROM quotation_items WHERE request_item_id = NEW.id;

  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

  -- Only run if quantity actually changed
  IF NEW.quantity != OLD.quantity THEN
    OPEN cur;

    read_loop: LOOP
      FETCH cur INTO qid;
      IF done THEN
        LEAVE read_loop;
      END IF;

      -- Recalculate the total_amount for each quotation
      UPDATE quotations
      SET total_amount = (
        SELECT SUM(qi.unit_price * ri.quantity)
        FROM quotation_items qi
        JOIN request_items ri ON qi.request_item_id = ri.id
        WHERE qi.quotation_id = qid
      )
      WHERE id = qid;

    END LOOP;

    CLOSE cur;
  END IF;
END;

-- Request_items trigger after item deletion
CREATE TRIGGER IF NOT EXISTS trg_before_request_item_delete
BEFORE DELETE ON request_items
FOR EACH ROW
BEGIN
  DECLARE done INT DEFAULT FALSE;
  DECLARE qid INT;

  -- Cursor must be declared after variables
  DECLARE cur CURSOR FOR
    SELECT quotation_id FROM quotation_items WHERE request_item_id = OLD.id;

  -- Handler after cursor
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

  OPEN cur;

  read_loop: LOOP
    FETCH cur INTO qid;
    IF done THEN
      LEAVE read_loop;
    END IF;

    -- Recalculate total BEFORE request_item is deleted
    UPDATE quotations
    SET total_amount = (
      SELECT IFNULL(SUM(qi.unit_price * ri.quantity), 0)
      FROM quotation_items qi
      JOIN request_items ri ON qi.request_item_id = ri.id
      WHERE qi.quotation_id = qid
        AND qi.request_item_id != OLD.id -- exclude the item being deleted
    )
    WHERE id = qid;

  END LOOP;

  CLOSE cur;
END;

