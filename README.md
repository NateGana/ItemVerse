# ITEMVERSE

## Install on XAMPP
1. Copy the `itemverse` folder into `C:\xampp\htdocs\`.
2. Start Apache and MySQL in the XAMPP Control Panel.
3. Open http://localhost/phpmyadmin, choose Import, select `database/itemverse.sql` and run it.
4. If your MySQL root user has a password, edit `config/db.php`.
5. Open http://localhost/itemverse/

## Admin login (local testing only)
Email: admin@itemverse.local
Password: Admin@123

## Sample members
reviewer1@itemverse.local, reviewer2@itemverse.local, reviewer3@itemverse.local (password Admin@123)

## Test checklist
1. Register a new account, log out, log in.
2. Search and filter on Discover, then tick up to three items and compare.
3. Open an item, submit a review with a photo, edit it, then delete it.
4. Press Helpful on someone else's review. Press Report, then approve it in the admin queue.
5. Save an item and see it on your dashboard. Update your profile.
6. As admin: add, edit and delete an item and a category, ban a user, and check the charts.
