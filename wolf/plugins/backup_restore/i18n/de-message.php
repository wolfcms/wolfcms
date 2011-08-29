<?php

/**
 * German language file
 *
 * @package Plugins
 * @subpackage backup_restore
 *
 * @author Tobias Urff <wolfcmsl10n_p@urff.at>
 * @author Patrick Scheips <patrick.scheips@simple-dev.org>, simple-dev.org
 * @version backup_restore 0.7.0
 */

return array(
	'Backup file was not uploaded correctly/completely or is broken.' => 'Sicherungskopie wurde nicht komplett/korrekt hochgeladen oder ist beschädigt.',
	'Backup settings' => 'Sicherungseinstellungen',
	'Backup/Restore plugin' => 'Sicherungs- und Wiederherstellungsplugin',
	'Create a backup' => 'Sicherungskopie erstellen',
	'Creating the backup' => 'Erstellen einer Sicherungskopie',
	'Current style' => 'Aktueller Stil',
	'Designed for Wolf version' => 'Entwickelt für Wolf-Version',
	'Do you want to download the backup as a zip file?' => 'Möchten Sie die Sicherungskopie als ZIP-Datei herunterladen?',
	'Do you want to include passwords in the backup file? <br/> If you select no, all passwords will be reset upon restoring the backup.' => 'Möchten Sie Passwörter in der Sicherungskopie mitsichern? Wenn Sie dies nicht tun, werden alle Passwörter bei der Wiederherstellung zurückgesetzt.',
	'Documentation' => 'Dokumentation',
	'Filename timestamp style' => 'Stil des Zeitstempels im Dateinamen',
	'If no password is provided in the backup file, reset all password fields to this default.' => 'Sollten sich in der Sicherungskopie keine Passwörter befinden, werden alle Passwörter auf diesen Standardwert zurückgesetzt.',
	'Include passwords' => 'Passwörter mitsichern',
	'Filename extension' => 'Dateinamenerweiterung',
	'What extension should be used for the filename.' => 'Welche Endung soll für den Dateinamen verwendet werden?',
	'No' => 'Nein',
	'Package as zip file' => 'ZIP-Archiv erstellen',
	'Provides administrators with the option of backing up their pages and settings to an XML file.' => 'Bietet Administratoren die Möglichkeit alle Seiten und Einstellungen über eine XML-Datei zu sichern und wiederherzustellen.',
	'Reset passwords to' => 'Passwörter zurücksetzen auf',
	'Restore a backup' => 'Sicherungskopie einspielen',
	'Restore settings' => 'Einstellungen wiederherstellen',
	'Restoring a backup' => 'Sicherungskopie einspielen',
	'Save' => 'Speichern',
	'Settings' => 'Einstellungen',
	'Succesfully restored backup.' => 'Sicherungskopie erfolgreich eingespielt.',
	'Successfully uninstalled plugin.' => 'Plugin erfolgreich deinstalliert.',
	'The Backup/Restore plugin allows you to create complete backups of the Wolf CMS core database.' => 'Das Sicherungs- und Wiederherstellungsplugin erlaubt es Ihnen vollständige Sicherungskopien der Wolf CMS-Datenbank zu erstellen.',
	'The settings have been saved.' => 'Die Einstellungen wurden gespeichert.',
	'This is an example of the filename that will be used for the generated XML file.' => 'Dies ist das Beispiel eines Dateinamens, der für die Generierung der XML-Datei verwendet wird.',
	'Unable to reconstruct table :tablename.' => 'Wiederherstellung der Tabelle :tablename fehlgeschlagen',
	'Unable to remove plugin settings.' => 'Entfernen der Plugin-Einstellungen fehlgeschlagen.',
	'Unable to truncate current table :tablename.' => 'Entleeren der aktuellen Tabelle :tablename fehlgeschlagen',
	'Upload plain text XML file' => 'Reintext-XML-Datei hochladen',
	'Version' => 'Version',
	'Warning!' => 'Warnung!',
    'and upwards.' => 'und neuer.',
	'What style of timestamp should be encorporated into the filename.' => 'In welchem Stil soll die aktuelle Zeit in den Dateinamen aufgenommen werden?',
	'Yes' => 'Ja',
	'You do not have permission to # the requested page!' => 'Sie haben keine Berechtigung um auf die angeforderte Seite zuzugreifen!',
	'You have modified this page.  If you navigate away from this page without first saving your data, the changes will be lost.' => 'Sie haben Änderungen an dieser Seite vorgenommen. Ihre Änderungen werden verworfen, wenn Sie diese Seite verlassen, ohne Ihre Änderungen vorher zu übernehmen.',
 	 'Are you sure you wish to restore?' => 'Sind Sie sich sicher, dass Sie die Daten wiederherstellen möchten?',
 	 'Backup Restore' => 'Sicherung / Wiederherstellung',

	// Sections of the documentation-View that didn't have l10n:
	'The Backup/Restore plugin allows you to create complete backups of the Wolf CMS database. It generates an XML file that contains all records for each of the Wolf CMS database tables.' => 'Das Sicherungs- und Wiederherstellungsplugin erlaubt es Ihnen eine komplette Sicherungskopie der Wolf CMS-Datenbank zu erstellen. Hierzu wird eine XML-Datei generiert, die alle Datensätze aller Datenbanktabellen enthält.',
	'To create and download the backup, simply select the "Create a backup" option.' => 'Um eine Sicherungskopie zu erstellen und herunterzuladen, wählen Sie die Option "Sicherungskopie erstellen".',
  	'By default, the download is generated in a zip file. If you want to download the plain unzipped XML file, go to the settings for this plugin and change the option there.' => 'Die Sicherungskopie wird standardmäßig als ZIP-Archiv erstellt. Sollten Sie bevorzugen, eine ungepackte Version der XML-Datei herunterzuladen, dann können Sie diese Option unter "Einstellungen" innerhalb dieses Plugins auswählen.',
 	 'Example:' => 'Beispiel:',
  	'To upload and restore a backup, simply select the "Restore a backup" option.' => 'Um eine Sicherungskopie hochzuladen und den Stand dieser wiederherzustellen, wählen Sie die Option "Sicherungskopie einspielen".',
  	'You can set a default password to enter into any password fields if the backup file does not contain passwords. For this to function, the system expects there to be password fields in the backup file with no value.' => 'Sie können ein Standardpasswort verwenden, welches für alle importierten Nutzerkonten verwendet wird. Hierzu darf das Passwortfeld in der XML-Datei keinen Wert enthalten.',

 	 // Sections of the restore-View that didn't have l10n:
  	'As such, the contents of your backup file will replace the contents of your Wolf CMS database tables.' => 'Diese Tabellen werden anschließend mit den Inhalten der Sicherungskopie befüllt.',
  	'Do NOT upload a zip file, only upload a plain text XML file!' => 'Bitte laden Sie eine Reintext-XML-Datei hoch. Es ist NICHT möglich, ein ZIP-Archiv hochzuladen!',
  	'Please be aware that <strong>all</strong> the database tables will be truncated when performing a restore. Truncating a table means that all records in that table are deleted.' => 'Bitte machen Sie sich bewusst, dass <strong>alle</strong> Datenbanktabellen entleert werden und dabei alle bisherigen Daten, die sich in der Wolf CMS-Datenbank befinden, verloren gehen.',
 	 'When restoring a backup, please make sure that the backup file was generated from the same Wolf CMS <em>version</em> as you are restoring it to.' => 'Wenn Sie eine Sicherungskopie einspielen und damit Ihre Wolf CMS-Installation auf den Stand der Sicherungskopie bringen, ist es wichtig sicherzustellen, dass die Sicherungskopie von einem Wolf CMS der selben Version stammt.',
);
