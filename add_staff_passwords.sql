-- Run this in phpMyAdmin to add password support for staff login
-- Go to phpMyAdmin > student_course_hub > SQL tab > paste this and click Go

-- Step 1: Add a password column to the Staff table
ALTER TABLE Staff ADD COLUMN Password VARCHAR(255) DEFAULT NULL;

-- Step 2: Add an email column (needed for login username)
ALTER TABLE Staff ADD COLUMN Email VARCHAR(255) DEFAULT NULL;

-- Step 3: Set default passwords for all existing staff
-- Default password for everyone is:  staff123
-- The long string below is the hashed version of "staff123" using PHP password_hash()
UPDATE Staff SET Password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                Email = CONCAT(LOWER(REPLACE(REPLACE(Name, 'Dr. ', ''), ' ', '.')), '@university.ac.uk')
WHERE Password IS NULL;

-- You can check the result with:
-- SELECT StaffID, Name, Email, Password FROM Staff;

-- NOTE: Each staff member can later change their password from the portal.
-- The default password for ALL staff is:  staff123
