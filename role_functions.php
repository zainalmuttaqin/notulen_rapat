<?php
// Definisi peran
define('ROLE_ADMIN', 1);
define('ROLE_OPERATOR_1', 2);
define('ROLE_OPERATOR_2', 3);
define('ROLE_OPERATOR_3', 4);
define('ROLE_PUBLIC', 5);

if (!function_exists('getRoleName')) {
    function getRoleName($id_role) {
        switch ($id_role) {
            case ROLE_ADMIN:
                return 'admin';
            case ROLE_OPERATOR_1:
                return 'operator 1';
            case ROLE_OPERATOR_2:
                return 'operator 2';
            case ROLE_OPERATOR_3:
                return 'operator 3';
            case ROLE_PUBLIC:
                return 'public';
            default:
                return 'Tidak diketahui';
        }
    }
}
?>