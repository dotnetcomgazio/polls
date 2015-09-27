<?php
	\OCP\Util::addStyle('polls', 'main');
	\OCP\Util::addScript('polls', 'start');
	OCP\User::checkLoggedIn();
?>

<h1><?php p($l->t('Summary')); ?></h1>
<form name="new_poll" method="POST">
	<input type="hidden" name="j" value="start"/>
	<input type="hidden" name="delete_id" value=""/>
	<input type="hidden" name="poll_id" value="" />
	<div class="goto_poll">
		<table class="cl_create_form">
			<tr>
				<th><?php p($l->t('Title')); ?></th>
				<th id="id_th_descr"><?php p($l->t('Description')); ?></th>
				<th class="cl_cell_width"><?php p($l->t('Created')); ?></th>
				<th><?php p($l->t('By')); ?></th>
				<th class="cl_cell_width"><?php p($l->t('Expires')); ?></th>
				<th><?php p($l->t('participated')); ?></th>
				<th id="id_th_descr"><?php p($l->t('Access')); ?></th>
			</tr>

			<?php
				//$query = OCP\DB::prepare('select * from *PREFIX*polls_events where (access =? and owner =?) or access !=? order by created');
				//$result = $query->execute(array('hidden', OCP\User::getUser(), 'hidden'));
				$query = OCP\DB::prepare('
				    select distinct *PREFIX*polls_events.id, *PREFIX*polls_events.type, *PREFIX*polls_events.title, *PREFIX*polls_events.description, *PREFIX*polls_events.owner, *PREFIX*polls_events.created, *PREFIX*polls_events.access, *PREFIX*polls_events.expire
				    from *PREFIX*polls_events
				    left join *PREFIX*polls_particip
				    on *PREFIX*polls_events.id = *PREFIX*polls_particip.id
				    left join *PREFIX*polls_comments
				    on *PREFIX*polls_events.id = *PREFIX*polls_comments.id
				    where
				    (*PREFIX*polls_events.access =? and *PREFIX*polls_events.owner =?)
				    or
				    *PREFIX*polls_events.access !=?
				    or
				    *PREFIX*polls_particip.user =?
				    or
				    *PREFIX*polls_comments.user =?
				    order by created');
				$result = $query->execute(array('hidden', OCP\User::getUser(), 'hidden', OCP\User::getUser(), OCP\User::getUser()));
			?>

			<?php while ($row = $result->fetchRow()) : ?>
				<?php  if (!userHasAccess($row['id'])) continue; ?>
				<tr>
					<td class="cl_link" title="<?php p($l->t('Go to')); ?>">
						<?php echo $row['title']; ?><input type="hidden" value="<?php echo $row['id']; ?>" />
					</td>
					<?php
						//$str = $row['description'];
						/*if (strlen($str) > 60){
							$str = substr($str, 0, 57) . '...';
						}*/
						//$str = wordwrap($str, 50, "<br/>", true);
					?>
					<td><div class="wordwrap"><?php echo $row['description']; ?></div></td>
					<?php //echo '<td>' . str_replace("_", " ", $row['created']) . '</td>'; ?>

					<?php
					// direct url to poll
					$url = \OCP\Util::linkToRoute('polls_index');
					$url = \OC_Helper::makeURLAbsolute($url);
					$url .= 'goto/' . $row['id'];
					?>

					<td class="cl_poll_url" title="<?php p($l->t('Click to get link')); ?>"><input type="hidden" value="<?php echo $url; ?>" /><?php echo date('d.m.Y H:i', $row['created']); ?></td>
					<td>
						<?php
							if($row['owner'] === OCP\User::getUser()) p($l->t('Yourself'));
							else echo OCP\User::getDisplayName($row['owner']); //echo OCP\User::getDisplayName($row['owner']);
						?>
					</td>
						<?php
						$style = '';
						if (isset($row['expire']) && $row['expire']) {
							if (date('U') > $row['expire']) {
								$style = ' style="color: red"';
							}
							echo '<td' . $style . '>' . date('d.m.Y', $row['expire']) . '</td>';
						}
						else {
							echo '<td />';
						}
						?>

                    <td class="column_center">
						<?php
							$partic_class = 'partic_no';
							for($i = 0; $i < count($partic_polls); $i++){
								if($row['id'] === $partic_polls[$i]['id']){
									$partic_class = 'partic_yes';
									array_splice($partic_polls, $i, 1);
									break;
								}
							}
						?>
						<div class="partic_all <?php echo $partic_class; ?>">
						</div>
						|
						<?php
							$partic_class = 'partic_no';
							for($i = 0; $i < count($partic_comm); $i++){
								if($row['id'] === $partic_comm[$i]['id']){
									$partic_class = 'partic_yes';
									array_splice($partic_comm, $i, 1);
									break;
								}
							}
						?>
						<div class="partic_all <?php echo $partic_class; ?>">
						</div>
                    </td>
					<td <?php if ($row['owner'] === OCP\User::getUser()) echo 'class="cl_poll_access" title="'.$l->t('Edit access').'"' ?> >
						<?php p($l->t($row['access'])); ?>
					</td>
					<?php if (strcmp($row['owner'], OCP\User::getUser()) === 0) : ?>
						<td title="delete poll" id="id_del_<?php echo $row['id']; ?>" class="cl_delete"><!--<?php p($l->t('delete')); ?>-->&#x2716;</td>
					<?php endif; ?>
				</tr>
			<?php endwhile; ?>
		</table>
	</div>
	<input type="submit" id="submit_new_poll" value="<?php p($l->t('Create new poll')); ?>" />
</form>

<?php include 'access_dialog.php'; ?>
<?php include 'footer.php'; ?>
