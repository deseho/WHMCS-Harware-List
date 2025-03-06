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
use WHMCS\Database\Capsule;

// Link unter "Weitere Aktionen" im Admin-Bereich hinzufügen
add_hook('AdminAreaClientSummaryActionLinks', 1, function ($vars) {
    return [
        '<a href="#" onclick="openHardwareModal(' . $vars['userid'] . ');return false;">🛠️ Hardware verwalten</a>',
    ];
});

// JavaScript und HTML für das Modal im Admin-Bereich hinzufügen
add_hook('AdminAreaFooterOutput', 1, function ($vars) {
    $modalHtml = <<<HTML
    <div id="hardwareModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="hardwareModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="hardwareModalLabel">Hardware verwalten</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Schließen">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="max-height: 75vh; overflow-y: auto;">
                    <div id="hardwareContent">Lade Daten...</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Schließen</button>
                </div>
            </div>
        </div>
    </div>
    HTML;

    $script = <<<SCRIPT
    <script type="text/javascript">
        function openHardwareModal(userId) {
            jQuery('#hardwareContent').html('Lade Daten...');
            jQuery('#hardwareModal').modal('show');
            jQuery.post('addonmodules.php?module=customerhardware&action=fetch&userid=' + userId, function(data) {
                jQuery('#hardwareContent').html(data);
            }).fail(function() {
                jQuery('#hardwareContent').html('Fehler beim Laden der Daten.');
            });
        }

        function saveHardwareItem(userId) {
            const formData = jQuery('#addHardwareForm').serialize();
            jQuery.post('addonmodules.php?module=customerhardware&action=save&userid=' + userId, formData, function(data) {
                if (data.success) {
                    alert('Hardware erfolgreich gespeichert.');
                    openHardwareModal(userId);
                } else {
                    alert('Fehler: ' + (data.message || 'Unbekannter Fehler'));
                }
            }, 'json').fail(function(xhr, status, error) {
                alert('Es ist ein Fehler aufgetreten: ' + error);
            });
        }

        function editHardwareItem(userId, hardwareId) {
            jQuery.post('addonmodules.php?module=customerhardware&action=edit&userid=' + userId + '&hardwareid=' + hardwareId, function(data) {
                jQuery('#hardwareContent').html(data);
            }).fail(function() {
                jQuery('#hardwareContent').html('Fehler beim Laden der Bearbeitungsansicht.');
            });
        }

        function updateHardwareItem(userId, hardwareId) {
            const formData = jQuery('#editHardwareForm').serialize();
            jQuery.post('addonmodules.php?module=customerhardware&action=update&userid=' + userId + '&hardwareid=' + hardwareId, formData, function(data) {
                if (data.success) {
                    alert('Hardware erfolgreich aktualisiert.');
                    openHardwareModal(userId);
                } else {
                    alert('Fehler: ' + (data.message || 'Unbekannter Fehler'));
                }
            }, 'json').fail(function(xhr, status, error) {
                alert('Es ist ein Fehler aufgetreten: ' + error);
            });
        }

        function deleteHardwareItem(userId, hardwareId) {
            if (confirm('Möchten Sie diesen Hardware-Eintrag wirklich löschen?')) {
                jQuery.post('addonmodules.php?module=customerhardware&action=delete&userid=' + userId + '&hardwareid=' + hardwareId, function(data) {
                    if (data.success) {
                        alert('Hardware erfolgreich gelöscht.');
                        openHardwareModal(userId);
                    } else {
                        alert('Fehler: ' + (data.message || 'Unbekannter Fehler'));
                    }
                }, 'json').fail(function(xhr, status, error) {
                    alert('Es ist ein Fehler aufgetreten: ' + error);
                });
            }
        }
    </script>
    SCRIPT;

    return $modalHtml . $script;
});

// Daten für das Modal bereitstellen (Admin-Bereich)
add_hook('AdminAreaPage', 1, function ($vars) {
    if (isset($_GET['module'], $_GET['action'], $_GET['userid'])) {
        $userId = (int) $_GET['userid'];

        if ($_GET['action'] === 'fetch') {
            try {
                $hardwareItems = Capsule::table('mod_customerhardware')->where('userid', $userId)->get();

                $output = '<table class="table table-bordered">';
                $output .= '<thead><tr><th>Gerät</th><th>S/N</th><th>IMEI</th><th>Bestelldatum</th><th>RE-Nummer</th><th>LI-Nummer</th><th>Lieferdatum</th><th>Bestellt bei</th><th>Aktionen</th></tr></thead><tbody>';

                foreach ($hardwareItems as $item) {
                    $output .= '<tr>';
                    $output .= '<td>' . htmlspecialchars($item->device_name) . '</td>';
                    $output .= '<td>' . htmlspecialchars($item->serial_number) . '</td>';
                    $output .= '<td>' . htmlspecialchars($item->imei) . '</td>';
                    $output .= '<td>' . htmlspecialchars($item->order_date) . '</td>';
                    $output .= '<td>' . htmlspecialchars($item->invoice_number) . '</td>';
                    $output .= '<td>' . htmlspecialchars($item->delivery_note_number) . '</td>';
                    $output .= '<td>' . htmlspecialchars($item->delivery_date) . '</td>';
                    $output .= '<td>' . htmlspecialchars($item->ordered_from) . '</td>';
                    $output .= '<td>
                        <span style="cursor: pointer; color: green;" onclick="editHardwareItem(' . $userId . ', ' . $item->id . ');">✏️</span>
                        <span style="cursor: pointer; color: red;" onclick="deleteHardwareItem(' . $userId . ', ' . $item->id . ');">🗑️</span>
                    </td>';
                    $output .= '</tr>';
                }

                $output .= '</tbody></table>';

                $output .= '<form id="addHardwareForm">';
                $output .= '<div class="form-group"><label>Gerät</label><input type="text" name="device_name" class="form-control" required></div>';
                $output .= '<div class="form-group"><label>S/N</label><input type="text" name="serial_number" class="form-control" required></div>';
                $output .= '<div class="form-group"><label>IMEI</label><input type="text" name="imei" class="form-control"></div>';
                $output .= '<div class="form-group"><label>Bestelldatum</label><input type="date" name="order_date" class="form-control"></div>';
                $output .= '<div class="form-group"><label>RE-Nummer</label><input type="text" name="invoice_number" class="form-control"></div>';
                $output .= '<div class="form-group"><label>LI-Nummer</label><input type="text" name="delivery_note_number" class="form-control"></div>';
                $output .= '<div class="form-group"><label>Lieferdatum</label><input type="date" name="delivery_date" class="form-control"></div>';
                $output .= '<div class="form-group"><label>Bestellt bei</label><input type="text" name="ordered_from" class="form-control"></div>';
                $output .= '<button type="button" class="btn btn-primary" onclick="saveHardwareItem(' . $userId . ');">Speichern</button>';
                $output .= '</form>';

                echo $output;
            } catch (Exception $e) {
                echo "Es ist ein Fehler aufgetreten: " . htmlspecialchars($e->getMessage());
            }
            exit;
        }

        if ($_GET['action'] === 'save') {
            header('Content-Type: application/json');

            try {
                Capsule::table('mod_customerhardware')->insert([
                    'userid' => $userId,
                    'device_name' => $_POST['device_name'],
                    'serial_number' => $_POST['serial_number'],
                    'imei' => $_POST['imei'],
                    'order_date' => $_POST['order_date'],
                    'invoice_number' => $_POST['invoice_number'],
                    'delivery_note_number' => $_POST['delivery_note_number'],
                    'delivery_date' => $_POST['delivery_date'],
                    'ordered_from' => $_POST['ordered_from'],
                ]);

                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            exit;
        }

        if ($_GET['action'] === 'edit') {
            $hardwareId = (int) $_GET['hardwareid'];

            try {
                $hardwareItem = Capsule::table('mod_customerhardware')->where('id', $hardwareId)->first();

                if ($hardwareItem) {
                    $output = '<form id="editHardwareForm">';
                    $output .= '<div class="form-group"><label>Gerät</label><input type="text" name="device_name" value="' . htmlspecialchars($hardwareItem->device_name) . '" class="form-control" required></div>';
                    $output .= '<div class="form-group"><label>S/N</label><input type="text" name="serial_number" value="' . htmlspecialchars($hardwareItem->serial_number) . '" class="form-control" required></div>';
                    $output .= '<div class="form-group"><label>IMEI</label><input type="text" name="imei" value="' . htmlspecialchars($hardwareItem->imei) . '" class="form-control"></div>';
                    $output .= '<div class="form-group"><label>Bestelldatum</label><input type="date" name="order_date" value="' . htmlspecialchars($hardwareItem->order_date) . '" class="form-control"></div>';
                    $output .= '<div class="form-group"><label>RE-Nummer</label><input type="text" name="invoice_number" value="' . htmlspecialchars($hardwareItem->invoice_number) . '" class="form-control"></div>';
                    $output .= '<div class="form-group"><label>LI-Nummer</label><input type="text" name="delivery_note_number" value="' . htmlspecialchars($hardwareItem->delivery_note_number) . '" class="form-control"></div>';
                    $output .= '<div class="form-group"><label>Lieferdatum</label><input type="date" name="delivery_date" value="' . htmlspecialchars($hardwareItem->delivery_date) . '" class="form-control"></div>';
                    $output .= '<div class="form-group"><label>Bestellt bei</label><input type="text" name="ordered_from" value="' . htmlspecialchars($hardwareItem->ordered_from) . '" class="form-control"></div>';
                    $output .= '<button type="button" class="btn btn-primary" onclick="updateHardwareItem(' . $userId . ', ' . $hardwareId . ');">Aktualisieren</button>';
                    $output .= '</form>';
                } else {
                    $output = '<p>Hardware-Eintrag nicht gefunden.</p>';
                }

                echo $output;
            } catch (Exception $e) {
                echo '<p>Es ist ein Fehler aufgetreten: ' . htmlspecialchars($e->getMessage()) . '</p>';
            }
            exit;
        }

        if ($_GET['action'] === 'update') {
            header('Content-Type: application/json');
            $hardwareId = (int) $_GET['hardwareid'];

            try {
                Capsule::table('mod_customerhardware')
                    ->where('id', $hardwareId)
                    ->update([
                        'device_name' => $_POST['device_name'],
                        'serial_number' => $_POST['serial_number'],
                        'imei' => $_POST['imei'],
                        'order_date' => $_POST['order_date'],
                        'invoice_number' => $_POST['invoice_number'],
                        'delivery_note_number' => $_POST['delivery_note_number'],
                        'delivery_date' => $_POST['delivery_date'],
                        'ordered_from' => $_POST['ordered_from'],
                    ]);

                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            exit;
        }

        if ($_GET['action'] === 'delete') {
            header('Content-Type: application/json');
            $hardwareId = (int) $_GET['hardwareid'];

            try {
                Capsule::table('mod_customerhardware')->where('id', $hardwareId)->delete();
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            exit;
        }
    }
});

// Kundenbereich: Hardware-Tabelle anzeigen (Gerät und S/N)
add_hook('ClientAreaHomepage', 1, function ($vars) {
    $userId = $_SESSION['uid'];

    try {
        $hardwareItems = Capsule::table('mod_customerhardware')
            ->where('userid', $userId)
            ->select('device_name', 'serial_number')
            ->get();

        $output = '<div class="panel panel-default">';
        $output .= '<div class="panel-heading"><h3 class="panel-title">📋 Meine Hardware</h3></div>';
        $output .= '<div class="panel-body">';
        $output .= '<table class="table table-bordered">';
        $output .= '<thead><tr><th>Gerät</th><th>S/N</th></tr></thead><tbody>';

        foreach ($hardwareItems as $item) {
            $output .= '<tr>';
            $output .= '<td>' . htmlspecialchars($item->device_name) . '</td>';
            $output .= '<td>' . htmlspecialchars($item->serial_number) . '</td>';
            $output .= '</tr>';
        }

        $output .= '</tbody></table>';
        $output .= '</div></div>';

        return $output;
    } catch (Exception $e) {
        return '<p>Es ist ein Fehler beim Abrufen der Hardware-Daten aufgetreten: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
});
