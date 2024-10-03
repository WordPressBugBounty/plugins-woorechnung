=== Faktur Pro for WooCommerce ===
Contributors: ZWEISCHNEIDER
Version: 3.1.5
Donate link: https://www.faktur.pro/
Tags: woocommerce, rechnung, lieferschein, buchhaltung, schnittstelle
Requires at least: 3.0.0
Requires WooCommerce: 3.0.0
Tested up to: 6.6.2
Tested PHP up to: 8.3
Stable tag: 3.1.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Faktur Pro ermöglicht Ihnen Rechnungen, Kunden und Produkte aus WooCommerce direkt in vielen Providern automatisch zu erzeugen.

== Description ==

Erstelle direkt völlig frei gestaltbare Rechnungen über WooCommerce oder verbinde es mit **Billomat**, **easybill**, **FastBill**, **lexoffice**, **Monsum by FastBill**, **sevDesk**. Faktur Pro ermöglicht es Dir Rechnungen, Gutschriften, Kunden und Produkte aus dem WordPress Plugin WooCommerce direkt zu erstellen und zu aktualisieren. Dadurch erhälst Du die Vorteile aus beiden Welten und sparst dabei sehr viel Zeit!

**Freie Gestaltung:**
Du bist der Herr über das Layout deiner Rechnung. Wähle aus verschiedenen Vorlagen und passe diese mit HTML und der Templatesprache Mustache nach deinen Vorstellungen an oder erstelle dein eigenes Layout!

**Automatisierte Abläufe:**
Erstellen und Aktualisieren von Kundendaten, Erzeugen von Rechnungen und Lieferscheinen bei Bestellungen und Gutschriften bei Widerruf. Bereitstellen von Rechnungen als Download und Erzeugen von Versandmarken – alles direkt in WooCommerce und alles automatisch.

**Multiple Shops:**
Verbinden Sie mehrere WooCommerce Shops mit Faktur Pro und behalten Sie den Überblick über Ihre gesamte Rechnungsstellung.

**Individuelle Layouts:**
Wählen Sie aus Ihren Rechnungsvorlagen und erstellen Sie stets zu Ihren hinterlegten Einstellungen passende und ansprechende Rechnungen.

**Registrierung**
Sie benötigen einen Faktur Pro Account. Registrieren Sie Ihren Account auf [faktur.pro](https://www.faktur.pro/ "Einfach Rechnungen über Billomat, easybill, FastBill, lexoffice, Monsum by FastBill oder sevDesk erzeugen")

== Installation ==

1. Laden Sie die Plugin-Daten in Ihr Verzeichniss `/wp-content/plugins/woorechnung`, oder installieren Sie das Plugin direkt über das WordPress Plugin-Management.
2. Aktivieren Sie Faktur Pro über den Menüpunkt 'Plugin'
3. Klicken Sie auf WooCommerce -> Einstellungen -> Faktur Pro um Ihre Einstellungen vorzunehmen. Den Lizenzkey finden Sie in Ihrem Faktur Pro Account.
4. Passen Sie die Einstellungen nach Ihren Vorstellungen an.

== Frequently Asked Questions ==

= Benötige ich einen Faktur Pro Account? =

Ja. Registrieren Sie sich unter https://www.faktur.pro und folgen Sie dem Setup-Prozess.

= Kann ich kostenlos Rechnungen erzeugen? =

Ja! Bis zu 10 Rechnungen im Monat sind bei Faktur Pro kostenlos. Wenn Sie mehr Rechnungen schreiben möchten, können Sie Ihren Account jederzeit upgraden.

= In welchem Format müssen die Preise angegeben werden? =

Wir empfehlen Preise immer in Netto anzugeben, da es oft zu Rundungsproblemen führt wenn WooCommerce die Bruttopreise zuerst in Netto umrechnet.

= Welche WooCommerce Version benötige ich? =

Faktur Pro benötigt mindestens WooCommerce 3 (oder höher).

== Changelog ==

0 3.1.5 (2024-09-25) =
* Fixed: Da beim Nutzen des Stripe Payment Gateway von WooCommerce mit der neuen Checkout Experience nicht immer ein Method Name existiert, werden jetzt in den FakturPro Einstellungen die Titel der Zahlungsmethode und in Klammern der Wert dazu angezeigt.

= 3.1.4 (2024-07-31) =
* Changed: Bestellung Aktionen zum Erstellen, Zurücksetzen und Ansehen von Rechnungen wird nicht mehr angezeigt wenn die Bestellung noch nie gespeichert wurde.
* Fixed: Fehler beim Senden einer separaten E-Mail mit einer E-Mail Vorlage.

= 3.1.3 (2024-07-01) =
* Changed: Erkennung von virtuellen und download Produkten geändert um eine Warnmeldung von WooCommerce zu vermeiden.
* Fixed: Erkennungsproblem des Steuersatzes von Produkten wenn in einem Land ein Steuersatz von 0% gesetzt ist. Vorher wurde es als kein vorhandener Steuersatz erkannt.

= 3.1.2 (2024-06-20) =
* Added: Error Meldung wenn die Rechnung nicht erstellt wurde beim Klick auf den Rechnung erstellen Button in der Bestellung Detail Seite.
* Fixed: Mehrere Warnungen und Fehler behoben die bei unterschiedlich gesetzten Warnstufen in den PHP Einstellungen vorkommen konnten.

= 3.1.1 (2024-05-31) =
* Added: Bestellung Aktionen und Buttons in der Bestellung Detail Seite zum Erstellen, Zurücksetzen und Downloaden einer Rechnung.
* Added: Kunde ist Mehrwertsteuer befreit und mehrwertsteuerfreie Rechnung werden jetzt an Faktur Pro übertragen und können in Texten auf der Rechnung über Konditions-Platzhalter abgefragt werden.
* Added: Wartezeit zu API Anfragen des gleichen Typs einer Bestellung zum Vermeiden von doppelten Anfragen direkt hinterienander durch 3rd Party Plugins.

= 3.1.0 (2024-05-22) =
* Added: Einstellung "E-Mail Vorlage" um optional eine WooCommerce E-Mail Vorlage für das Senden einer Rechnung zu verwenden.
* Added: Einstellung "Metaname der Umsatzsteuer-ID" mit der optional ein Meta-Name angegeben werden kann, über den die Umsatzsteuer-ID aus den Metadaten der Bestellung oder des Kunden ausgelesen werden soll.
* Added: Platzhalter Variable "company_or_name" hinzugefügt.
* Added: Umsatzsteuer-ID Erkennung für das 3rd Party Plugin "Wholesale for WooCommerce".
* Changed: Rechnung erstellen Button Icon wird rot dargestellt wenn beim letzten Versuch ein Fehler aufgetreten ist.
* Changed: Platzhalter Variablen können jetzt mit den Klammern { und } verwendet werden. Beispiel: "{order_date}".
* Changed: Erkennung ob eine Bestellung keine physischen Produkte hat, die geliefert werden müssen, damit beim Anbieter sevDesk im Fall von Kunde im EU-Ausland keine "Innergemeinschaftliche Lieferung" genutzt wird.

= 3.0.21 (2024-02-12) =
* Added: In den Einstellungen können jetzt weitere Empfänger (TO), Kopie Empfänger (CC) und Blindkopie Empfänger (BCC) angegeben werden.
* Fixed: Beim Versenden der Rechnung über eine separate E-Mail wurden die Kopie Empfänger (CC) nicht korrekt formatiert hinzugefügt.

= 3.0.20 (2024-02-28) =
* Fixed: Ein Problem beim Auslesen von bestimmten Produktdaten wie z.B. Warenkorbkurzbeschreibung wurde behoben.

= 3.0.19 (2024-02-14) =
* Added: Einstellung "Preis Dezimalstellen" erlaubt die Nutzung von mehr als 2 Dezimalstellen bei Produktpreisen unabhängig von der Darstellung der Preise im Shop.
* Added: Neue Platzhalter Variablen "%order_id%", "%invoice_date%" und "%invoice_date_de%" für Dateinamen und E-Mail Texte.
* Added: Neue Platzhalter Variablen "%order_date%", "%order_date_de%", "%order_date_day%", "%order_date_month%" und "%order_date_year%" für E-Mail Texte.
* Fixed: Platzhalter Variable "%order_no%" nutzte bisher die ID der Bestellung und wurde jetzt zur Bestellnummer geändert.

= 3.0.18 (2024-01-24) =
* Fixed: Bei Verwendung der Einstellung "Guthaben zusammenführen" wurde bei Bestellungen ohne Guthaben immer eine Rechnungsposition mit 0,00 Euro Guthaben erstellt.

= 3.0.17 (2024-01-24) =
* Fixed: Problem mit bestimmten PHP Versionen und der Strict Error Reporting Einstellung behoben.

= 3.0.16 (2024-01-23) =
* Added: Einstellung "Alternativer Titel" als Produktbeschreibung.
* Added: Unterstützung von verwendeten Guthaben durch das Plugin "Smart Coupons for WooCommerce".
* Changed: Fehlermeldung bei fehlgeschlagener Rechnungserstellung wird jetzt an der Bestellung als "individuelles Feld" hinterlegt und geprüft, damit bei einer Bestellung nicht mehrmals versucht wird eine Rechnung zu erstellen.

= 3.0.15 (2024-01-18) =
* Added: Umsatzsteuer-ID Erkennung für das 3rd Party Plugin "WooCommerce EU/UK VAT Compliance".
* Added: Unterstützung von 3rd Party Plugins welche den Produkten bei WooCommerce Untertitel hinzufügen. Unterstützt werden die Plugins "Secondary Title" und "Product Subtitle For WooCommerce", sowie Plugins die Metadaten zum Produkt mit folgenden Namen hinzufügen: "alternate_title", "alt_title", "secondary_title", "subtitle", "alternate_name", "alt_name" oder "secondary_name".

= 3.0.14 (2023-11-30) =
* Added: Senden der Daten beim Setzen des Bestellstatus auf "storniert" damit Faktur Pro eine Stornorechnung erstellen kann.

= 3.0.13 (2023-11-24) =
* Fixed: Fehler beim Erkennen der Umsatzsteuer-ID behoben, der durch die Ändeurngen im Update 3.0.12 hinzu gekommen war.

= 3.0.12 (2023-11-23) =
* Added: Unterstützung von 3rd Party Plugins welche die Umsatzsteuer-ID am Kunden anstatt der Bestellung hinterlegen.

= 3.0.11 (2023-10-22) =
* Changed: Englische Übersetzung hinzugefügt

= 3.0.10 (2023-10-17) =
* Added: Unterstützung zur Aufteilung von Steuern auf Versandkosten für das Plugin "Germanized".
* Added: Weitere Optionen zur Einstellung "Produktbeschreibung" hinzugefügt.

= 3.0.9 (2023-10-09) =
* Added: Erkennung der Umsatzsteuer-ID für das Plugin "German Market" hinzugefügt.

= 3.0.8 (2023-08-22) =
* Added: Unterstützung für HPOS (High-Performance Order Storage) "Data storage for orders" von WooCommerce.
* Changed: Erkennung von Umsatzsteuer ID erweitert um mehr 3rd Party Plugins zu unterstützen.
* Fixed: Mehrfachaktionen (Bulk-Actions) "Rechnungen exportieren" und "Rechnungen zurücksetzen" wurden in neueren Versionen von WooCommerce nicht mehr angezeigt.
* Fixed: Namenskorrektur eines Fehlerfalls.
* Fixed: Unterstützung für Unix-Timestamp Format beim Rechnungsdatum "fakturpro_invoice_date" der Bestellungen.

= 3.0.7 (2022-11-08) =
* Added: In sevDesk kann der Ansprechpartner nun gewählt werden
* Fixed: Installations-Wizzard optimiert

= 3.0.6 (2022-08-23) =
* Added: Dritte Migration (Update) der Faktur Pro Metadaten-Namen der Bestellungen.

= 3.0.5 (2022-08-11) =
* Added: Zweite Migration (Update) der Faktur Pro Metadaten-Namen der Bestellungen.

= 3.0.4 (2022-08-03) =
* Fixed: Aktualisierung bei Update Routine der Datenbankwerte von Faktur Pro optimiert für Shops mit vielen Bestellungen.

= 3.0.3 (2022-07-26) =
* Fixed: Problem mit "null" bei Meldungen für den Adminbereich behoben.

= 3.0.2 (2022-07-26) =
* Fixed: Änderung bzgl. Fehlermeldung einer fehlenden Funktion von Wordpress.

= 3.0.1 (2022-07-14) =
* Changed: Umstellung der API Anfragen von direkter Curl Verwendung auf Wordpress Remote HTTP Funktionen.

= 3.0.0 (2022-07-12) =
* Rebranding von ehemals WooRechnung zu Faktur Pro

= 2.3.0 (2022-05-17) =
* Added: Lexoffice als neuen Rechnungs-Provider hinzugefügt.
* Fixed: Zeigt bei Multisites netzwerkweite Plugin Aktivierungen nun keine fehlerhafte Fehlermeldung mehr an bezüglich noch fehlende WooCommerce Aktivierung.
* Fixed: Unter umständen konnte eine Fehlermeldung beim Stornieren von Rechnungen bei Billomat entstehen. Das wurde behoben.

= 2.2.2 (2022-04-14) =
* Fixed: In der neuen WooCommerce Version wurden die Bundesländer in ISO-Codes gewandelt. Wir zeigen diese nun wieder ausgeschrieben auf der Rechnung.
* Fixed: Fehlermeldung erstellt, sollte WooCommerce nicht installiert oder aktiviert sein.

= 2.2.1 (2021-11-09) =
* Added: Unterstützung für WooCommerce 5.9.0
* Fixed: Fehler behoben, durch den Rechnungen nicht an WooCommerce-Mails angehangen wurden, wenn die Rechnungen erst durch die gleiche Bestellstatusänderung erstellt wurden.

= 2.2.0 (2021-10-19) =
* Added: Unterstützung für WordPress 5.8.1
* Added: Unterstützung für WooCommerce 5.8.0
* Added: Unterstützung von Custom Order Status Plugins. Benutzerdefinierte Bestellstati können jetzt als Trigger für das Erstellen von Rechnungen sowie den Versand von Faktur Pro Mails ausgewählt werden.
* Fixed: Fehlermeldung in der WordPress Health Detection behoben, indem Faktur Pro nicht mehr länger PHP Sessions zum Speichern von Session-Daten nutzt.

= 2.1.1 (2021-07-26) =
* Fixed: Fehlende Dateien in WordPress Update
* Fixed: Inkonsistentes Whitespacing im Code

= 2.1.0 (2021-07-26) =
* Added: Unterstützung für WordPress 5.8
* Added: Unterstützung für WooCommerce 5.5.2
* Added: Unterstützung für WooCommerce Subscriptions. Rechnungen für Folgebestellungen können jetzt auch an die von WooCommerce Subscriptions versandten E-Mails angehangen werden.
* Added: Verbesserte Auswahl der Produktbeschreibungen für Shops, die sowohl variable als auch einfache Produkte vertreiben.
* Added: Benachrichtigungen, falls eine Rechnung aufgrund zwischenzeitlich gelöschter Produkte nicht mehr erstellt werden kann.
* Fixed: Sehr seltenen Fehler behoben, der bei Bestellungen mit vielen Bestellpositionen und unterschiedlichen Umsatzsteuersätzen die falschen Umsatzsteuersätze zu manchen Produkten auf der Rechnung ausweisen konnte.
* Fixed: Unnötige Zeichen aus dem Download der .zip-Datei der Mehrfachaktion 'Rechnungen exportieren' entfernt.

= 2.0.23 (2021-01-08) =
* Added: Support für WordPress 5.6
* Added: Support für WooCommerce 4.8.0
* Fixed: Vereinzelt aufgetretener get_plugin_data Fehler bei Bestellabschluss behoben

= 2.0.22 (2020-11-05) =
* Added: Support für WordPress 5.5.3 und WooCommerce 4.6.1
* Added: Verschiedene neue Variablen, die im Dateinamen der Rechnung genutzt werden können
* Added: Unterstützung zu EU-VAT Plugins, die die VAT-Nummer als _billing_eu_vat_number, _vat_number oder vat_number speichern
* Fixed: Zu Bestellungen, auf die die Mehrfachaktion "Rechnung zurücksetzen" angewandt wird, wird nicht mehr automatisch eine neue Rechnung generiert, falls die Bestellung in einem zur Erzeugung von Rechnungen eingestellten Status ist
* Fixed: Verbesserte Übertragung von rabattierten Bestellpositionen zwecks klarerer Darstellung auf den Rechnungsdokumenten

= 2.0.21 (2020-09-24) =
* Added: Support für WordPress 5.5.1
* Added: Support für WooCommerce 4.5.2
* Fixed: Kompatibilität mit Wunschlisten Plugin

= 2.0.20 (2020-08-31) =
* Fixed: Änderungen an "posix_getpwnam" aus Version 2.0.19 um Fatal Error zu vermeiden.

= 2.0.19 (2020-08-28) =
* Fixed: Verbesserung der Abfrage der PHP Einstellung "open_basedir", um bei Verwendung dieser PHP Einstellung Fehler zu umgehen

= 2.0.18 (2020-08-15) =
* Fixed: Entfernung von festen Datentypen bei Funktionsrückgaben um Fehler mit älteren PHP Versionen zu vermeiden

= 2.0.17 (2020-08-12) =
* Added: Support für WordPress 5.5 und WooCommerce 4.3.2
* Added: Die Einstellungen des Plugins sind jetzt aus der Plugin-Übersicht heraus verlinkt
* Added: %first_name% und %last_name% können als Variablen für Vor- und Nachname der Rechnungsadresse im Dateinamen der Rechnung genutzt werden
* Fixed: Unterstützung für Klassenerweiterungen von alternativen Mailer-Plugins, die nicht direkt die PHPMailer Klasse nutzen
* Fixed: Abfrage der PHP Einstellung "open_basedir", um bei Verwendung dieser PHP Einstellung Fehler zu umgehen

= 2.0.16 (2020-07-22) =
* Added: Es können jetzt mehrere WooCommerce E-Mails zum Versand der Rechnung konfiguriert werden
* Fixed: Fehler behoben, bei dem 0€ Produkte nicht mit dem hinterlegten Umsatzsteuersatz übertragen wurden
* Fixed: Fehler behoben, bei dem die Rechnung im Status "Zahlung ausstehend" nicht automatisiert erstellt werden konnte
* Fixed: Problem behoben, bei dem die Rechnung bei Verwendung anderer Plugins, die den Zeitpunkt des Versands der WooCommerce E-Mails beeinflussen, nicht an die E-Mail angehangen wurde
* Fixed: Problem behoben, dass beim Speichern sehr komplexer .html-Templates zum Versand der Rechnung als separate E-Mail aufgetreten ist

= 2.0.15 (2020-04-24) =
* Changed: Anpassung der Namen von Bestellstati an die aktuelle WooCommerce Übersetzung
* Changed: Änderungen an den Einstellungen zum E-Mail Versand der Rechnung
* Added: Dienstleistungs-Produkte werden nach Möglichkeit als solche bei Buchhaltungsanbietern gespeichert
* Added: Der Platzhalter %invoice_no% kann ebenfalls im Betreff oder Text von E-Mails verwendet werden, wenn die Rechnungsnummer referenziert werden soll
* Added: Der Platzhalter %order_no% kann in Text und Betreff von E-Mails, sowie im Dateinamen der Rechnung genutzt werden, um die WooCommerce Bestellnummer zu referenzieren
* Added: Die separate Rechnungs-E-Mail kann nun auch zeitversetzt nach Erstellung der Rechnung versandt werden
* Added: Die Rechnung kann jetzt auch an andere WooCommerce-Mails als die Bestellbestätigung angehangen werden
* Fixed: Fehler behoben, bei dem trotz gegenteiliger Konfiguration Rechnungen über 0€ erstellt wurden
* Fixed: Fehler behoben, der den Versand von Textmails verhindern konnte, solange keine HTML-Mail hinterlegt war
* Fixed: Fehler behoben, bei dem die Rechnung an durch andere Plugins versandte E-Mails angehangen wurde

= 2.0.14 (2019-07-17) =
* Added: Übertragung der Kundenanmerkungen zu Bestellungen
* Fixed: Behebung des fehlschlagenden E-Mail-Versands bei fehlenden Einstellungen
* Fixed: Behebung einer PHP-Warning bei Rechnugnserstellung

= 2.0.13 (2019-05-17) =
* Fixed: Rechnungsnummer wird (wenn vorhanden) in das Plugin zurückübertragen
* Fixed: Exportierte Rechnungen werden nach Rechnungsnummer benannt
* Fixed: Per E-Mail versendete Rechnungen können mit Platzhalter %invoice_no% benannt werden
* Fixed: Fehler beim Anhang der Rechnung an E-Mail werden besser abgefangen

= 2.0.12 (2019-04-29) =
* Added: Versandkosten können benannt werden
* Fixed: Versandkosten bei mix MwSt. Bestellungen werden nun richtig berechnet

= 2.0.11 (2019-04-25) =
* Added: Die Bestellnummer wird nun als Rechnungsname genommen
* Added: Verbesserte Fehlermeldungen

= 2.0.10 (2019-04-17) =
* Fixed: Versandkostenbeschreibung entfernt
* Fixed: Mit 0€ Rechnungen
* Fixed: Kompatibilität mit YITH Gift Card Plugin

= 2.0.9 (2019-04-17) =
* Fixed: MwSt. Berechnung bei Versandkosten

= 2.0.8 (2019-04-16) =
* Fixed: Rechnungs Bulk Export geht wieder

= 2.0.6 (2019-04-15) =
* Fixed: Rechnung als bezahlt markieren
* Fixed: Stornierungen gehen wieder
* Fixed: Nicht 0% MwSt. bei 0€ Versand
* Fixed: Variable Produktbeschreibung funktioniert wieder

= 2.0.0 (2019-04-10) =
* Changed: Komplettes Code-Refactoring

= 1.1.8 (2019-02-13) =
* Added: Vorbereitung auf unser neues großes Release

= 1.1.7 (2018-08-23) =
* Fixed: VATID aus Plugins werden nun besser erkannt

= 1.1.6 (2018-07-25) =
* Added: Du kannst nun pro Zahlungsart ganz einfach die Texte auf deinen Rechnungen anpassen. So kannst du auch die Zahlungsdaten deines Zahlungsanbieters auf die Rechnung schreiben sollte jemand per "Kauf auf Rechnung" bezahlen. https://www.faktur.pro/anleitung/kauf-auf-rechnung

= 1.1.5 (2018-06-27) =
* Changed: Gutschein in Rabatt umbenannt

= 1.1.4 (2018-06-15) =
* Fixed: Actions Spalte wird nun automatisch aktiviert beim speichern des Lizenzkeys

= 1.1.3 (2018-05-02) =
* Fixed: Einige Fixes für großes Systeme und neuem Faktur Pro

= 1.1.2 (2018-03-14) =
* Fixed: WooCommerce 3 Optimierungen

= 1.1.1 (2018-01-17) =
* Added: Nun wird ein Webhook nach der Rechnungserstellung von unserem Server zu deinem WordPress gesendet um sicher zu gehen dass die Daten der Rechnung in die Bestellung gespeichert wurden. Dadurch beheben wir doppelte Rechnungen bei Abbrüchen.
* Fixed: Beschleunigung der Erstellung
* Fixed: Bessere Fehlermeldungen
* Fixed: Zahlungsarten von älteren WooCommerce Systemen werden wieder erkannt

= 1.1.0 (2017-11-18) =
* Added: 0€ Rechnungen können nun aktiviert werden
* Added: Weitere Platzhalter für die Freitexte (E-Mail und VatID)
* Added: Bei sevDesk kann die Betreffzeile nun frei gewählt werden
* Added: Faktur Pro kann nun auch Bruttorechnungen erzeugen
* Fixed: FastBill UNIT_PRICE Fehler bei 0€ Positionen

= 1.0.9 (2017-11-17) =
* Added: Mehr Daten stehen zum Export bereit
* Added: FastBill VatID wird mit übertragen
* Added: Download Namen der Rechnungen wurde angepasst so das die Rechnungsnummer als Name erscheint
* Fixed: Bessere Fehlermeldungen (401 & 403)
* Fixed: sevDesk erkennt nun mehr Länder
* Fixed: payment_method_title wurde angepasst da mit der neusten WooCommerce Version nicht mehr kompatibel

= 1.0.8 (2017-10-05) =
* Added: pebe smart & 1&1 Buchhaltung als Anbieter hinzugefügt
* Added: Bei Rechnungen in die USA wird der State mit in die PLZ gespeichert
* Fixed: Das Gewicht von Produkten mit Varianten wird nun auch richtig berechnet

= 1.0.7 (2017-09-20) =
* Fixed: Faktur Pro: Wenn eine Bestellung storniert wurde, werden keine doppelten Rechnungen mehr erzeugt

= 1.0.6 (2017-09-19) =
* Added: Du kannst nun mehrere Stati für die Rechnungserstellung als auch den Mailversand wählen. Einfach mit STRG + Klick (WIN) oder CMD + Klick (OSX) mehrere auswählen und speichern
* Added: Anbindung zu pebesmart.ch und online-buchhaltung.1und1.de
* Fixed: Der Rechnungsexport exportiert nun mehr als 200 Zeilen

= 1.0.5 (2017-08-24) =
* Added: Rechnungen haben nun beim downloaden als auch in den E-Mails die Rechnungsnummer als Namen
* Added: Lieferadresse wurde als Platzhalter für die Einleitung als auch Schlußtext hinzugefügt
* Added: Wir haben den Anbieter "Ohne Anbieter" in "Faktur Pro" geändert

= 1.0.4 (2017-08-18) =
* Fixed: Undefined Fehler behoben

= 1.0.3 (2017-08-17) =
* Added: Rechnungen bei "Faktur Pro" können nun frei gestaltet werden. Du benötigst nur Kenntnisse in HTML und der Template-Sprache Mustache
* Added: Export Funktionen unter faktur.pro - Historie
* Added: Du kannst einstellen ob die MwSt. ohne oder mit einer Nachkommastelle Gerundet werden. Dies ist z.B. wichtig für die MwSt. von 2,5% in den Schweiz
* Added: Das Feld Staat (State) wird nun an alle Anbieter übermittelt
* Added: "Faktur Pro" fügt nun auch das Land mit auf die Rechnung wenn es sich um eine Rechnung ins Ausland handelt
* Added: Du kannst einstellen ob die Rechnungen gedownloaded oder direkt im Browser in einem neuen Tab angezeigt werden
* Fixed: Debitoor MwSt. Rundungsprobleme

= 1.0.2 (2017-07-31) =
* Added: Vorbereitungen für eine Export-Funktion
* Added: Zu jeder Bestellung wird nun auch die Rechnungsnummer gespeichert

= 1.0.1 (2017-07-25) =
* Added: Postversand der Rechnungen
* Fixed: Es werden keine doppelten Rechnungen mehr erzeugt
* Fixed: Undefined Index entfernt

= 1.0.0 (2017-07-07) =
* Added: Verbessertes logging bei Debitoor
* Fixed: 1 Cent Rungundsprobleme sind nun behoben
* Fixed: Doppelte Statusmeldungen beim speichern der WordPress Einstellungen

= 0.9.14 (2017-06-22) =
* Added: Option damit eine Versandmarke nach dem Erstellen direkt in einem neuen Tab geöffnet wird
* Added: Automatische Berechnung des Gewichtes für Versandmarken anhand der Produkte
* Added: Default Preset kann gewählt werden. So wird die Versandmarke unten immer automatisch damit befüllt
* Fixed: sevDesk Login Methode funktioniert nun wieder
* Fixed: Undefined Fehlermeldungen bei WooCommerce 3 behoben

= 0.9.13 (2017-06-14) =
* Fixed: Kompatibilitätsproblem mit WooCommerce 3.0.8

= 0.9.11 (2017-06-12) =
* Added: Du kannst bei sevDesk nun ein Zahlungsziel auswählen
* Added: Wir haben die Kleinunternehmerregelung zu "Faktur Pro" hinzugefügt
* Added: Wir haben die Schweiz als Land für Debitoor hinzugefügt
* Added: Wir haben Österreich als Land für Debitoor hinzugefügt
* Fixed: Wir erzeugen keine doppelten Rechnungen mehr wenn Status der Rechnung als auch des E-Mail Versandes gleich sind
* Fixed: sevDesk: Intro- und Outrotext funktioniert nun mit Absätzen
* Fixed: easybill: Länder werden nun richtig gespeichert
* Fixed: Wir erzeugen keine Kunden mehr mit leeren E-Mailadressen
* Fixed: Debitoor: MwSt werden nun pro Land einzeln betrachtet
* Fixed: Faktur Pro: Bezeichnung Bestellung in Rechnung geändert

= 0.9.10 (2017-03-01) =
* Added: Shipcloud Versicherungsbetrag kann eingegeben werden

= 0.9.9 (2017-02-28) =
* Added: Die Rechnungsnummer von "Faktur Pro" kann nun durch weitere Variablen verfeinert werden
* Added: VAT Nummern aus dem Plugin "WooCommerce EU VAT Number" von WooThemes
* Fixed: Log "Permission" Probleme behoben
* Fixed: Debitoor funktioniert nun auch mit anderen Währungen als €

= 0.9.8 (2017-02-27) =
* Fixed: Undefined Variable

= 0.9.7 (2017-02-23) =
* Added: sevDesk Kleinunternehmerregelung kann nun aktiviert werden
* Added: sevDesk Bruttorechnungen können ab jetzt erzeugt werden
* Added: bei FastBill werden nun mehr Zahlungsarten erkannt und richtig zugewiesen
* Fixed: Versicherungs Datum wird nun wieder unter den Produkten angezeigt

= 0.9.6 (2017-02-20) =
* Added: VAT-Nummer zu "Faktur Pro" hinzugefügt
* Fixed: Logger Fehlermeldung beim ersten Nutzen entfernt
* Fixed: Versicherung wird nun über "Fees" hinzugefügt
* Fixed: sevDesk Titel "Invoice" ind "Rechnung" geändert
* Fixed: Text Bestellung in Bestellnummer geändert
* Fixed: API Calls beschleunigt

= 0.9.5 (2017-01-26) =
* Added: Debitoor speichert nun auch die VatID (Plugin: WooCommerce EU VAT Assistant)
* Fixed: Debitoor Rechnungsnummer als Dateinamen für E-Mail und Download der Rechnungen

= 0.9.4 (2017-01-09) =
* Added: Sollten Rechnungen zu dem Zeitpunkt des E-Mail Versandes noch nicht erzeugt worden sein, werden diese nun erzeugt

= 0.9.3 (2017-01-05) =
* Added: Rechnungen können nun wie früher an bestehende WooCommerce E-Mails angehangen werden. Dazu gibt es eine neue Option in den Rechnungseinstellungen
* Added: Du findest deine Rechnungen nun in deinem faktur.pro Account
* Fixed: Billomat: Rechnungsdatum funktioniert nun wie gewollt
* Fixed: Debitoor: Produkte können nun wieder aktuallisiert werden
* Fixed: Debitoor: z.Hd. wird nur noch gezeigt sollte auch ein Vor- / Nachname angegeben worden sein
* Fixed: Debitoor: Einige Probleme mit mehreren MwSt. Sätzen auf einer Rechnung wurden behoben
* Fixed: Easybill: ZIP Code wird nun wieder richtig gespeichert
* Fixed: Easybill: Berechnung bei Brutto Preisen funktioniert nun wieder

= 0.9.2 (2016-12-02) =
* Added: Lokale Log Datei für bessere Supportmöglichkeiten
* Fixed: API Key lässt keine Leerzeichen mehr zu
* Fixed: FastBill Adress2 wird nicht mehr in das Dokument eingefügt wenn diese leer ist

= 0.9.1 (2016-11-11) =
* Fixed: Rechnungen werden nun im TEMP Ordner des Systems geschrieben um Schreibfehler zu vermeiden
* Fixed: Wording

= 0.9.0 (2016-11-02) =
* Added: E-Mails werden ab jetzt über eine eigene E-Mail verschickt. Somit beheben wir das Problem das bei einigen Shops die Rechnung nicht an E-Mails angehangen wurde. Ihr könnt die E-Mail selbständig Layouten. Geht dazu unter WordPress - WooCommerce - Faktur Pro auf Rechnungs E-Mail.
* Fixed: Es wurden Probleme mit der Verbindung zu sevDesk gelöst
* Fixed: Mit Billomat können nun auch andere Währungen als €genutzt werden

= 0.8.5 (2016-09-28) =
* Fixed: Manche Plugins verändern Preise von 0 zu NAN was zu problemen führte

= 0.8.4 (2016-09-26) =
* Fixed: Für Debitoor Kunden die als Hauptsitz nicht Deutschland haben, wird mit diesem Fix die Funktionalität von Faktur Pro ermöglicht

= 0.8.3 (2016-09-23) =
* Fixed: gzip entfernt da es bei einigen Systemen Probleme erzeugt (Kryptische Zeichen wurden angezeigt)

= 0.8.2 (2016-08-29) =
* Added: Fees werden nun auf der Rechnung angezeigt

= 0.8.1 (2016-08-29) =
* Added: Mehr Länder zur Auswahl für Versandmarken
* Fixed: "Undefined Index" entfernt

= 0.8.0 (2016-08-24) =
* Added: Gutscheine erscheinen nun als Posten auf der Rechnung
* Added: Das Plugin "WooCommerce Pay for Payment" von Jörn Lund ist ab jetzt kompatibel mit Faktur Pro
* Fixed: Preisberechnung verändert so das Gutscheine nicht mehr von den jeweiligen Post abgezogen werden sondern als eigener Posten auf der Rechnung erscheint

= 0.7.7 (2016-08-22) =
* Added: Produktkurzbeschreibung kann nun auf der Rechnung gespeichert werden
* Added: Anrede und Zusatzadresse werden gespeichert
* Fixed: FastBill: Versand-Land wird nun mit gespeichert

= 0.7.6 (2016-08-17) =
* Fixed: sevDesk Währungen werden nun übernommen
* Fixed: Rechnungen werden nun auch wieder mit der standard Mailfunktion verschickt (Wir empfehlen dennoch den Versand per SMTP Plugin z.B. WP Mail Bank)
* Fixed: Besseres Debug Log
* Fixed: easybill probleme mit kleinere Paketen gelöst
* Fixed: Versandkosten berechnung nach Update auf WooCommerce 2.6 gefixt

= 0.7.5 (2016-08-01) =
* Added: Im kostenlosen Tarif haben wir die Versandmarken von 5 auf 25 geändert
* Added: sevDesk Produkte können nun gespeichert und bearbeitet werden
* Added: Das Limit kann nun aufgehoben werden so dass mehr Rechnungen oder Versandmarken einzeln abgerechnet werden können
* Fixed: Debitoor Lieferscheine können nun auch für andere Länder als Deutschland ausgestellt werden
* Fixed: Fehlermeldungen von Debitoor und sevDesk vereinfacht
* Fixed: Debitoor Rechnungserstellung nach Großbritannien nun möglich

= 0.7.4 (2016-07-18) =
* Added: FastBill kann nun pro Land ein eigenes Template nutzen
* Fixed: Rechnungsicon

= 0.7.3 (2016-07-18) =
* Added: E-Mail Status kann nun selbständig gewählt werden
* Added: Adresse 2 wird mit übergeben
* Fixed: "Faktur Pro" Berechnung

= 0.7.2 (2016-07-16) =
* Added: Rechnungen können nun in jedem Status manuell erzeugt werden
* Added: Bei "Faktur Pro" kann die Währung nun geändert werden
* Added: Shipcloud kann nun Faktur Pro Kunden direkt erkennen und besser Supporten
* Added: Bei Debitoor kann das Zahlungsdatum frei gewählt werden
* Added: Bei Debitoor wird der Versand bei verschiedenen MwSt Sätzen auf einer Rechnung nun nach deutschem Recht gesplittet
* Fixed: FastBill UNIT_PRICE

= 0.7.1 (2016-07-08) =
* Added: Debitoor Lieferscheine können nun automatisch mit erzeugt werden.

= 0.7.0 (2016-07-04) =
* Added: sevDesk Anbindung
* Fixed: Undefined index

= 0.6.11 (2016-07-04) =
* Added: Status "Wartend" als Rechnungsstatus hinzugefügt
* Added: Bei versandmarken kann optional das E-Mail Feld frei gelassen werden
* Fixed: Bei den Debitoor Kundenakten werden keine leeren Kundennummern gespeichert
* Fixed: UPS wird nun bei der Versandmarkenerzeugung vorausgewählt
* Fixed: Bei EasyBill konnten keine Rechnungen erzeugt werden

= 0.6.10 (2016-06-28) =
* Fixed: Fehlerbehebung aus Version 0.6.9

= 0.6.9 (2016-06-28) =
* Added: Unterstüzung des Plugins "Shipping Details for WooCommerce" von "PatSaTECH". Der Trackingcode und der Versandanbieter werden automatisch ausgefüllt.
* Fixed: Undefined index

= 0.6.8 (2016-06-25) =
* Added: Multilanguage Support
* Fixed: Die Produktbeschreibung wird nur noch bei dem richtigen Setting mit übergeben

= 0.6.7 (2016-06-22) =
* Added: Einheit wird mit an die Rechnung übergeben
* Added: Unterstüzung für Monsum (ehemals FastBill Automatic)
* Added: Debitoor - Schlußtext mit Variablen hinzugefügt (Auch für Kleinunternehmer Klausel geeignet)
* Added: Faktur Pro - Artikelbeschreibung hinzugefügt
* Fixed: Faktur Pro - Logos werden nun resized

= 0.6.6 (2016-06-18) =
* Fixed: Variationsproblem seit WooCommerce 2.6.0 jetzt behoben

= 0.6.5 (2016-06-17) =
* Added: Bulk Download der Rechnungen
* Added: Unterstützung von "WooCommerce Order Status Manager"

= 0.6.4 (2016-06-15) =
* Added: Versandmarken Vorlagen für schnelleren Versand
* Fixed: Einige kleine Änderungen für WooCommerce 2.6.0

= 0.6.3 (2016-06-13) =
* Added: Nun können Rechnungen auch ohne externen Anbieter erzeugt werden

= 0.6.2 (2016-05-30) =
* Added: FastBill Introtext kann mit Platzhaltern versehen und überschrieben werden
* Added: WooRent integration
* Added: Die Verbindung zwischen Faktur Pro und WordPress kann nun getestet werden
* Fixed: FastBill Zahlungsarten werden nun richtig zu den Kunden gespeichert

= 0.6.1 (2016-05-18) =
* Added: DEBUG Konsole bei Fehlermeldungen

= 0.6.0 (2016-05-16) =
* Added: Rechnungs- / Zahlungsstatus pro Zahlungsart wählbar
* Added: Rechnungen können ab jetzt auch als Entwurf gespeichert werden (Debitoor & FastBill)
* Fixed: Briefporto über shipcloud
* Fixed: TaxEnabled Fehler bei Debitoor behoben

= 0.5.10 =
* Added: Deutsche Post AG (Briefe und Buchsendungen) können nun per shipcloud verschickt werden

= 0.5.9 =
* Fixed: Rechnungen konnten nicht erzeugt werden. Ist nun wieder gefixt. Entschuldigt!

= 0.5.8 =
* Added: FastBill - Rechnungen werden als Bezahlt markiert

= 0.5.7 =
* Added: Das Rechnungsdatum kann in den Einstellungen geändert werden (Tag der Bestellung oder Tag der Rechnungserzeugung)
* Added: Versandkosten-Artikelnummer ist nun änderbar
* Fixed: Versandkostenberechnung mit unterschiedlichen "Germanize" Plugins gefixt

= 0.5.6 =
* Fixed: Automatische Rechnungen werden nun auch erzeugt, wenn die Einstellungen noch nicht gespeichert wurden

= 0.5.5 =
* Fixed: Rechnungen werden nun wieder automatisch erzeugt

= 0.5.4 =
* Added: Weitere Länder zu den Versandmarken hinzugefügt
* Fixed: Schnellere Ladezeiten

= 0.5.3 =
* Aktion: Wir haben die Preise um -50% verringert!
* Fixed: Einige Anpasungen

= 0.5.2 =
* Fixed: Bessere Fehlermeldungen für Rechnungen und shipcloud

= 0.5.1 =
* Added: Rechnungserstellung kann nun deaktiviert werden falls gewünscht
* Fixed: Splittung der Einstellungen in eigene Tabs

= 0.5.0 =
* Added: shipcloud

= 0.4.5 =
* Added: Prefix und Suffix für die Bestellnummer
* Added: Debitoor - Lieferadresse kann mit in den Rechnungs-Hinweis gespeichert werden
* Added: Vorbereitungen für shipcloud: Dieses Feature folgt in der nächsten Version
* Fixed: Versandkosten Artikelnummer auf "vk" reduziert

= 0.4.4 =
* Fixed: Leerzeichen vor und nach dem Lizenzkey werden nun ignoriert
* Fixed: Das Rechnungsicon in der Bestelübersicht wird nun auch bei anderen Stati als Fertiggestellt angezeigt
* Fixed: Die Rechnung wird nun mit der E-Mail versendet, zu der die Rechnung erzeugt wird

= 0.4.3 =
* Added: FastBill Brutto-Rechnung
* Added: Billomat Anbindung
* Added: easybill Anbindung

= 0.4.2 =
* Added: Es werden keine Rechnungen für 0€ Bestellungen erstellt

= 0.4.1 =
* Fixed: Artikelnummer bei Variationen wird nun auf der Rechnung angezeigt
* Fixed: Bei mehreren Steuersätzen auf einer Rechnung werden die Versandkosten nun anteilig aufgeteilt

= 0.4.0 =
* Fixed: Undefined index Fehler
* Added: Der Variantentitel kann nun als Produktbeschreibung an die Rechnung übergeben werden
* Added: Die Lieferadresse wird nun in FastBill zu den Kundendaten gespeichert

= 0.3.9 =
* Fixed: Debitoor - Rundungsfehler behoben
* Added: Nur noch ein request pro Rechnungserstellung
* Added: Debitoor - Produkte können nun gespeichert und bearbeitet werden
* Added: Artikelnummer wird mit übertragen
* Added: Zusätzlich zum Produktnamen wird nun auch die Produktbeschreibung gespeichert
* Added: Option um Produktbeschreibung anzupassen

= 0.3.8 =
* Fixed: "unexpected end of file" behoben

= 0.3.7 =
* Fixed: Bestellnummer

= 0.3.6 =
* Added: Nun speichert Faktur Pro die Bestellnummer zu den Rechnungen

= 0.3.5 =
* Fixed: MwSt. Berechnung

= 0.3.4 =
* Rechnungen automatisch versenden

= 0.3.3 =
* Erstelle, downloade und storniere Rechnungen automatisch bei der Bearbeitung deiner WooCommerce bestellungen
* Rechnungs-Provider: Debitoor, FastBill und Automatic by FastBill

== Upgrade Notice ==

= 0.3.3 =
Erste Version
