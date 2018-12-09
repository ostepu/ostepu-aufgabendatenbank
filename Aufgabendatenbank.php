<?php

#region Aufgabendatenbank

class Aufgabendatenbank {

    private static $initialized = false;
    public static $name = 'Aufgabendatenbank';
    public static $installed = false;
    public static $page = 3;
    public static $rank = 75;
    public static $enabledShow = true;
    public static $enabledInstall = true;
    private static $langTemplate = 'Aufgabendatenbank';

    public static function getDefaults() {
        return array(
            'ad_passwd' => array('data[AD][ad_passwd]', null)
        );
    }

    /**
     * initialisiert das Segment
     * @param type $console
     * @param string[][] $data die Serverdaten
     * @param bool $fail wenn ein Fehler auftritt, dann auf true setzen
     * @param string $errno im Fehlerfall kann hier eine Fehlernummer angegeben werden
     * @param string $error ein Fehlertext für den Fehlerfall
     */
    public static function init($console, &$data, &$fail, &$errno, &$error) {
        Installation::log(array('text' => Installation::Get('main', 'functionBegin')));
        Language::loadLanguageFile('de', self::$langTemplate, 'json', dirname(__FILE__) . '/');
        Installation::log(array('text' => Installation::Get('main', 'languageInstantiated')));

        $def = self::getDefaults();

        $text = '';
        $text .= Design::erstelleVersteckteEingabezeile($console, $data['AD']['ad_passwd'], 'data[AD][sqa_passwd]', $def['ad_passwd'][1], true);
        echo $text;
        self::$initialized = true;
        Installation::log(array('text' => Installation::Get('main', 'functionEnd')));
    }
    
    public static function show($console, $result, $data) {
        // das Segment soll nur gezeichnet werden, wenn der Nutzer eingeloggt ist
        if (!Einstellungen::$accessAllowed) {
            return;
        }
        
        if (!Paketverwaltung::isPackageSelected($data, 'Aufgabendatenbank')){
            return;
        }

        Installation::log(array('text' => Installation::Get('main', 'functionBegin')));
        $text = '';

        if (!$console) {
            $text .= Design::erstelleBeschreibung($console, Installation::Get('main', 'description', self::$langTemplate));
        }

        $text .= Design::erstelleZeile($console, Installation::Get('main', 'setPasswd', self::$langTemplate), 'e', Design::erstelleEingabezeile($console, $data['AD']['ad_passwd'], 'data[AD][ad_passwd]', '', true), 'v');

        echo Design::erstelleBlock($console, Installation::Get('main', 'title', self::$langTemplate), $text);
        Installation::log(array('text' => Installation::Get('main', 'functionEnd')));
        return null;
    }
    
    // dem Segment Komponentenzugang werden die Profile für die Aufgabendatenbank hinzugefügt
    public static function getAllExternalProfiles($data){
        if (!Paketverwaltung::isPackageSelected($data, 'Aufgabendatenbank')){
            return array();
        }
        
        if (!isset($data['AD']['ad_passwd'])){
            $data['AD']['ad_passwd'] = '';
        }
        
        $myProfile = GateProfile::createGateProfile(null,
                                                    'AD');
                                                     
        $myProfile->addRule(GateRule::createGateRule(null,
                                                     'httpCall',
                                                     'LExerciseSheet',
                                                     'GET /exercisesheet/exercisesheet/:sheetid/(:exercise)',
                                                     null));
                                                     
        $myProfile->addRule(GateRule::createGateRule(null,
                                                     'httpCall',
                                                     'LExerciseSheet',
                                                     'GET /exercisesheet/course/:courseid/(:exercise)',
                                                     null));
                                                     
        $myProfile->addRule(GateRule::createGateRule(null,
                                                     'httpCall',
                                                     'DBCourse',
                                                     'GET /course',
                                                     null));
                                                     
        $myProfile->addRule(GateRule::createGateRule(null,
                                                     'httpCall',
                                                     'DBCourse',
                                                     'GET /course/course/:courseid',
                                                     null));
                                                     
        $myProfile->addAuth(GateAuth::createGateAuth(null,
                                                     'httpAuth',
                                                     null,
                                                     'AD',
                                                     $data['AD']['ad_passwd'],
                                                     null));

        return array($myProfile);
    }

}

#endregion SQaLibur