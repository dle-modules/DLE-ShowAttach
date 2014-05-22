<?php
/*
=============================================================================
Show Attach - модуль вывода информации об аттачменте для для DLE
=============================================================================
Автор:   ПафНутиЙ
URL:     http://pafnuty.name/
twitter: https://twitter.com/pafnuty_name
google+: http://gplus.to/pafnuty
email:   pafnuty10@gmail.com
=============================================================================
*/

if (!defined('DATALIFEENGINE')) die("Go fuck yourself!");

$cfg = array(
	'newsId' => !empty($news_id) ? (int) $news_id : false,
	'template' => !empty($template) ? $template : 'showattach/default',
	'multiple' => !empty($multiple) ? true : false
);
if ($cfg['newsId']) {

	// Запрос в БД
	$row = $db->super_query("SELECT id, news_id, name, onserver, date, dcount FROM " . PREFIX . "_files WHERE news_id = '" . $cfg['newsId'] . "'", $cfg['multiple']);
	$tpl->load_template($cfg['template'] . '.tpl');
	if (!$cfg['multiple']) {
		$rows[] = $row;
	} else {
		$rows = $row;
	}

	foreach ($rows as $attachItem) {
		if ($rows[0]['id']) {
			$onserver = $attachItem['onserver'];
			$md5      = md5_file(ROOT_DIR . '/uploads/files/' . $onserver);
			$size     = formatsize(@filesize(ROOT_DIR . '/uploads/files/' . $onserver));


			$tpl->set('{attach_id}', $attachItem['id']);
			$tpl->set('{attach_name}', $attachItem['name']);
			$tpl->set('{attach_size}', $size);
			$tpl->set('{attach_md5}', $md5);

			$rowDate = $attachItem['date'];
			if (date('Ymd', $rowDate) == date('Ymd', $_TIME)) {
				$tpl->set('{attach_date}', $lang['time_heute'] . langdate(", H:i", $rowDate));
			} elseif (date('Ymd', $rowDate) == date('Ymd', ($_TIME - 86400))) {
				$tpl->set('{attach_date}', $lang['time_gestern'] . langdate(", H:i", $rowDate));
			} else {
				$tpl->set('{attach_date}', langdate($config['timestamp_active'], $rowDate));
			}

			$tpl->copy_template = preg_replace_callback("#\{attach_date=(.+?)\}#i", "formdate", $tpl->copy_template);

			$tpl->set('{attach_download}', $attachItem['dcount']);

			$tpl->set( '[attach]', "" );
			$tpl->set( '[/attach]', "" );
			$tpl->set_block( "'\\[not-attach\\](.*?)\\[/not-attach\\]'si", "" );

			if ($user_group[$member_id['user_group']]['allow_files']) {
				$tpl->set( '[allow-attach]', "" );
				$tpl->set( '[/allow-attach]', "" );
				$tpl->set_block( "'\\[not-allow-attach\\](.*?)\\[/not-allow-attach\\]'si", "" );
			} else {
				$tpl->set( '[not-allow-attach]', "" );
				$tpl->set( '[/not-allow-attach]', "" );
				$tpl->set_block( "'\\[allow-attach\\](.*?)\\[/allow-attach\\]'si", "" );
			}

		} else {
			$tpl->set_block( "'\\[attach\\](.*?)\\[/attach\\]'si", "" );
			$tpl->set( '[not-attach]', "" );
			$tpl->set( '[/not-attach]', "" );
		}

		$tpl->compile('showAttach');
	}

	$showAttach = $tpl->result['showAttach'];

	$tpl->clear();

	// Выводим результат работы модуля
	echo $showAttach;
}
?>
