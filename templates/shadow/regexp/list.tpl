<?php
for($i = 0; isset($regexp[$i]); $i++) {
	if($regexp[$i]['matching']) {
		$regexp[$i]['reg_exp'] = '<span class="found">'.$regexp[$i]['reg_exp'].'</span>';
	}
	if($regexp[$i]['active'] == 1) {
		$regexp[$i]['reg_exp']	= $input->checkbox('expr[]', $regexp[$i]['ID']).$regexp[$i]['reg_exp'];
	}
	else {
		$regexp[$i]['reg_exp']	= $input->checkbox('expr[]', $regexp[$i]['ID']).'<span class="deactivated">'.$regexp[$i]['reg_exp'].'</span>';
	}
}
$regexp = array_densify($regexp, array('dest'));
for($i = 0; isset($regexp[$i]); $i++) {
	if(count($regexp[$i]['dest'][0]) < $cfg['address']['hide_threshold'])
		$regexp[$i]['dest'][0] = implode('<br />', $regexp[$i]['dest'][0]);
	else
		$regexp[$i]['dest'][0] = '<span class="quasi_btn">'.sprintf(txt('96'), count($regexp[$i]['dest'][0])).' &raquo;</span><div><span class="quasi_btn">&laquo; '.sprintf(txt('96'), count($regexp[$i]['dest'][0])).'</span><br />'.implode('<br />', $regexp[$i]['dest'][0]).'</div>';
}
?>
<form action="<?= mkSelfRef() ?>" method="post">
<?= caption(txt('33').'&nbsp;'.$oma->current_user->used_regexp.'/'.$oma->current_user->max_regexp, getPageList('<a href="'.mkSelfRef(array('regx_page' => '%d')).'">%d</a>', $oma->current_user->used_regexp, $_SESSION['limit'], $_SESSION['offset']['regx_page']), 580) ?>
<?php outer_shadow_start(); ?>
<table border="0" cellpadding="1" cellspacing="1">
	<tr>
		<td class="std" width="430"><b><?= txt('18') ?></b></td>
		<td class="std" width="150"><b><?= txt('19') ?></b></td>
	</tr>
	<?php foreach($regexp as $entry) { ?>
		<tr>
			<td class="std"><?= implode('<br />', $entry['reg_exp']) ?></td>
			<td class="std addr_dest"><?= $entry['dest'][0] ?></td>
		</tr>
	<?php } ?>
</table>
<?php outer_shadow_stop(); ?>