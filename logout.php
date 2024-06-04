<?php
// Sāk sesiju, ja tā jau nav sākta
session_start();

// Notīra sesijas datus
session_unset();

// Izbeidz sesiju
session_destroy();

// Pāradresē uz sākumlapu
header("Location: /");

// Pārtrauc skripta izpildi
exit;
?>
