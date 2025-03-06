<?php
/*
 * Copyright (C) 2025 DeSeHo.com
 *
 * Alle Rechte vorbehalten.
 *
 * Dieser Quellcode ist urheberrechtlich geschützt.
 * Die Vervielfältigung, Verbreitung, öffentliche Zugänglichmachung oder
 * anderweitige Nutzung dieses Codes (auch auszugsweise) ist ohne
 * ausdrückliche schriftliche Genehmigung des Urhebers strengstens untersagt.
 */
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

function customerhardware_config() {
    return [
        "name" => "Customer Hardware",
        "description" => "Verwalten Sie Hardware-Einträge für Kunden im WHMCS Admin-Panel.",
        "version" => "1.1",
        "author" => "DeSeHo.com",
        "fields" => []
    ];
}

function customerhardware_activate() {
    $query = "CREATE TABLE IF NOT EXISTS `mod_customerhardware` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `userid` INT NOT NULL,
        `device_name` VARCHAR(255) NOT NULL,
        `serial_number` VARCHAR(255) NOT NULL,
        `imei` VARCHAR(255) NULL,
        `order_date` DATE NULL,
        `invoice_number` VARCHAR(255) NULL,
        `delivery_note_number` VARCHAR(255) NULL,
        `delivery_date` DATE NULL,
        `ordered_from` VARCHAR(255) NULL,
        `date_added` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`userid`) REFERENCES `tblclients`(`id`) ON DELETE CASCADE
    )";
    full_query($query);
    return ["status" => "success", "description" => "Das Modul wurde erfolgreich aktiviert."];
}

function customerhardware_deactivate() {
    $query = "DROP TABLE IF EXISTS `mod_customerhardware`";
    full_query($query);
    return ["status" => "success", "description" => "Das Modul wurde erfolgreich deaktiviert."];
}

function customerhardware_output($vars) {
    echo "<p>Das Modul 'Customer Hardware' ist aktiv.</p>";
}
