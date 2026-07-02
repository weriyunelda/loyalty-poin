<?php

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "loyalty-poin"
);

if (!$conn) {
    die("Koneksi Database Gagal");
}

mysqli_set_charset($conn, "utf8mb4");
