<?php
    session_unset();
    session_destroy();
    session_commit();
    header("Location: ../index.php");
?>