<?php echo password_hash('admin123', PASSWORD_DEFAULT); ?>
$2y$10$nME5Nld7H.iUTd5oRcsxfOLd.maw4wzv9KWAZ72BABMPShHWejRau

// Verify the password

<?php
// Hash a password
$password = "my_secure_password";
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Display the hashed password
echo "Hashed Password: " . $hashedPassword . "\n";

// Verify the password
$inputPassword = "my_secure_password"; // User input
if (password_verify($inputPassword, $hashedPassword)) {
    echo "Password is valid!";
} else {
    echo "Invalid password!";
}
?>
