<?php

// Initialization
//
require( 'auth.php' );

OpenLibrary( 'configman.library' );
OpenLibrary( 'locale.library' );
OpenLibrary( 'hui.library' );
OpenLibrary( 'ampshared.library' );
OpenLibrary( 'jpcache.library' );

$gEnv['runtime']['modules']['jpcache']['cacheon'] = 0;

$gLocale = new Locale( 'jpcache_root_jpcache', $gEnv['root']['locale']['language'] );

$gHui = new Hui( $gEnv['root']['db'] );
$gHui->LoadWidget( 'amppage' );
$gHui->LoadWidget( 'amptoolbar' );
$gHui->LoadWidget( 'xml' );

$gPage_content = $gStatus = $gToolbars = '';
$gTitle = $gLocale->GetStr( 'jpcache.title' );
$gMenu = get_ampoliros_root_menu_def( $gEnv['root']['locale']['language'] );

$gToolbars['main'] = array(
                           'main' => array(
                                           'label' => $gLocale->GetStr( 'default.button' ),
                                           'themeimage' => 'configure',
                                           'action' => build_events_call_string( '', array( array( 'main', 'default', '' ) ) )
                                          ),
                           'stats' => array(
                                           'label' => $gLocale->GetStr( 'stats.button' ),
                                           'themeimage' => 'folder',
                                           'action' => build_events_call_string( '', array( array( 'main', 'stats', '' ) ) )
                                          ),
                           'erase' => array(
                                            'label' => $gLocale->GetStr( 'erasecache.button' ),
                                            'themeimage' => 'edittrash',
                                            'action' => build_events_call_string( '', array(
                                                                                            array( 'main', 'default', '' ),
                                                                                            array( 'action', 'erasecache', '' ) ) ),
                                            'needconfirm' => 'true',
                                            'confirmmessage' => $gLocale->GetStr( 'erase.confirm' )
                                           )
                          );
/*
$gToolbars['help'] = array( 'help' => array(
                                            'label' => $gLocale->GetStr( 'help.button' ),
                                            'themeimage' => 'help',
                                            'action' => build_events_call_string( '', array( array( 'main', 'help', '' ) ) )
                                           ) );
*/

// Action dispatcher
//
$gAction_disp = new HuiDispatcher( 'action' );

$gAction_disp->AddEvent( 'setprefs', 'action_setprefs' );
function action_setprefs( $eventData )
{
    global $gEnv, $gStatus, $gLocale;

    $jp_cfg = new ConfigFile( CONFIG_PATH.'jpcache.cfg', true );

    $jp_cfg->SetValue( 'CACHE_TIME', $eventData['cachetime'] );
    $jp_cfg->SetValue( 'CACHE_ON',   $eventData['cacheon']   == 'on' ? '1' : '0' );
    $jp_cfg->SetValue( 'USE_GZIP',   $eventData['usegzip']   == 'on' ? '1' : '0' );
    $jp_cfg->SetValue( 'CACHE_POST', $eventData['cachepost'] == 'on' ? '1' : '0' );
    $gStatus = $gLocale->GetStr( 'prefs_set.status' );
}

$gAction_disp->AddEvent( 'erasecache', 'action_erasecache' );
function action_erasecache( $eventData )
{
    global $gEnv, $gStatus, $gLocale;

    cache_erase();

    $gStatus = $gLocale->GetStr( 'cache_erased.status' );
}

$gAction_disp->Dispatch();

// Main dispatcher
//
$gMain_disp = new HuiDispatcher( 'main' );

$gMain_disp->AddEvent( 'default', 'main_default' );
function main_default( $eventData )
{
    global $gEnv, $gLocale, $gPage_content;

    $jp_cfg = new ConfigFile( CONFIG_PATH.'jpcache.cfg', true );

    $xml_def =
'<vertgroup><name>prefs</name><children>
  <form><name>prefs</name><args><method>post</method><action type="encoded">'.urlencode( build_events_call_string( '', array(
                                                                                                                             array( 'main', 'default', '' ),
                                                                                                                             array( 'action', 'setprefs', '' )
                                                                                                                            ) ) ).'</action></args><children>
    <grid><name>prefs</name><children>
      <label row="0" col="0"><name>cachetime</name><args><label type="encoded">'.urlencode( $gLocale->GetStr( 'cachetime.label' ) ).'</label></args></label>
      <string row="0" col="1"><name>cachetime</name><args><disp>action</disp><size>5</size><value>'.$jp_cfg->Value( 'CACHE_TIME' ).'</value></args></string>
      <label row="1" col="0"><name>cacheon</name><args><label type="encoded">'.urlencode( $gLocale->GetStr( 'cacheon.label' ) ).'</label></args></label>
      <checkbox row="1" col="1"><name>cacheon</name><args><disp>action</disp><checked>'.( $jp_cfg->Value( 'CACHE_ON' ) == '1' ? 'true' : 'false' ).'</checked></args></checkbox>
      <label row="2" col="0"><name>usegzip</name><args><label type="encoded">'.urlencode( $gLocale->GetStr( 'usegzip.label' ) ).'</label></args></label>
      <checkbox row="2" col="1"><name>usegzip</name><args><disp>action</disp><checked>'.( $jp_cfg->Value( 'USE_GZIP' ) == '1' ? 'true' : 'false' ).'</checked></args></checkbox>
      <label row="3" col="0"><name>cachepost</name><args><label type="encoded">'.urlencode( $gLocale->GetStr( 'cachepost.label' ) ).'</label></args></label>
      <checkbox row="3" col="1"><name>cachepost</name><args><disp>action</disp><checked>'.( $jp_cfg->Value( 'CACHE_POST' ) == '1' ? 'true' : 'false' ).'</checked></args></checkbox>
    </children></grid>
  </children></form>
  <horizbar><name>hb</name></horizbar>
  <button><name>submit</name>
    <args>
      <label type="encoded">'.urlencode( $gLocale->GetStr( 'setprefs.submit' ) ).'</label>
      <themeimage>button_ok</themeimage>
      <horiz>true</horiz>
      <frame>true</frame>
      <formsubmit>prefs</formsubmit>
      <action type="encoded">'.urlencode( build_events_call_string( '', array(
                                                                                                                             array( 'main', 'default', '' ),
                                                                                                                             array( 'action', 'setprefs', '' )
                                                                                                                            ) ) ).'</action>
    </args>
  </button>
</children></vertgroup>';

    $gPage_content = new HuiXml( 'page', array( 'definition' => $xml_def ) );
}

$gMain_disp->AddEvent( 'stats', 'main_stats' );
function main_stats( $eventData )
{
    global $gEnv, $gLocale, $gPage_content;

    $xml_def =
'<vertgroup><name>stats</name><children>
    <grid><name>prefs</name><children>
      <label row="0" col="0"><name>cacheobjects</name><args><label type="encoded">'.urlencode( $gLocale->GetStr( 'cacheobjects.label' ) ).'</label></args></label>
      <string row="0" col="1"><name>cacheobjects</name><args><readonly>true</readonly><size>5</size><value>'.cache_objects().'</value></args></string>
    </children></grid>
</children></vertgroup>';

    $gPage_content = new HuiXml( 'page', array( 'definition' => $xml_def ) );
}

$gMain_disp->Dispatch();

// Rendering
//
$gHui->AddChild( new HuiAmpPage( 'page', array(
                                               'pagetitle' => $gTitle,
                                               'menu' => $gMenu,
                                               'toolbars' => array(
                                                                   new HuiAmpToolBar( 'main', array(
                                                                                                    'toolbars' => $gToolbars
                                                                                                   ) ) ),
                                               'maincontent' => $gPage_content,
                                               'status' => $gStatus
                                               ) ) );
$gHui->Render();

?>