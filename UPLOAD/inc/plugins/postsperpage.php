<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.");
}

$plugins->add_hook('showthread_start','postsperpage_run');

function postsperpage_info()
{
	return array(
		"name"			=> "Posts per Page",
		"description"	=> "Overwrites the default post per page settings of specific threads",
		"website"		=> "https://github.com/SvePu/MyBB-Post-per-Page",
		"author"		=> "SvePu",
		"authorsite"	=> "https://community.mybb.com/user-91011.html",
		"version"		=> "1.0",
		"guid" 			=> "",
		"codename"		=> "postsperpage",
		"compatibility" => "18*"
	);
}

function postsperpage_activate()
{
	global $db, $mybb;

	$query_add = $db->simple_select("settinggroups", "COUNT(*) as rows");
	$rows = $db->fetch_field($query_add, "rows");	
	$setting_group = array(
		'name' => 'postsperpage_settingsgroup',
		'title' => 'Posts per Page Settings',
		'description' => 'Settings of Posts per Page plugin',
		'disporder' => $rows+1,
		'isdefault' => 0
	);

	$gid = $db->insert_query("settinggroups", $setting_group);

	$setting_array = array(
		'postsperpage_enable' => array(
			'title' => 'Enable Post per Page Plugin?',
			'description' => 'Choose YES to enable it!',
			'optionscode' => 'yesno',
			'value' => '1',
			'disporder' => 1
		),
		'postsperpage_threads' => array(
			'title' => 'Thread IDs',
			'description' => 'Enter the thread ids where you want to set a new limit - (separated by comma)',
			'optionscode' => 'text',
			'value' => '',
			'disporder' => 2
		),
		'postsperpage_newlimit' => array(
			'title' => 'New Posts per Page Limit',
			'description' => 'Enter the number of posts per page for the specified threads',
			'optionscode' => "numeric",
			'value' => 10,
			'disporder' => 3
		)
	);

	foreach($setting_array as $name => $setting)
	{
		$setting['name'] = $name;
		$setting['gid'] = $gid;

		$db->insert_query('settings', $setting);
	}

	rebuild_settings();
}

function postsperpage_deactivate()
{
	global $mybb, $db;	
	
	$result = $db->simple_select('settinggroups', 'gid', "name = 'postsperpage_settingsgroup'", array('limit' => 1));
	$group = $db->fetch_array($result);
	
	if(!empty($group['gid']))
	{
		$db->delete_query('settinggroups', "gid='{$group['gid']}'");
		$db->delete_query('settings', "gid='{$group['gid']}'");
		rebuild_settings();
	}
}

function postsperpage_run()
{
	global $mybb, $thread;
	if($mybb->settings['postsperpage_enable'] != 1 || empty($mybb->settings['postsperpage_threads']) || $mybb->settings['postsperpage_newlimit'] < 1)
	{
		return;
	}
	if(in_array($thread['tid'], explode(",", $mybb->settings['postsperpage_threads'])))
	{
		$mybb->settings['postsperpage'] = $mybb->settings['postsperpage_newlimit'];
	}
}
