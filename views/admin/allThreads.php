<div id="content-body">
	<section class="title">
		<h4><?=lang('mahana:all_threads'); ?></h4>
	</section>
	
	<section class="item">
		<div class="content">
			<?php if (!empty($threads)): ?>
				
				<table>
					<thead>
						<tr>
							<th>Thread id</th>
							<th><?php echo isset($subject_column) ? $subject_column : lang('mahana:subject'); ?></th>							
							<th><?php echo lang('mahana:participants'); ?></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach( $threads['retval'] as $thread ): ?>
						<tr>
							<td><?=$thread['thread_id']?></td>
							<td><?=$thread['subject']?></td>
							<td>
								<?php if(!$thread['participants']['err']) : foreach($thread['participants']['retval'] as $key=>$participant) : ?>
									<a href="<?=site_url();?>admin/users/edit/<?=$participant['user_id'];?>"><?=$participant['display_name'];?></a>
									<?php if(isset($thread['participants']['retval'][$key+1])): ?>& <?php endif;?>
								<?php endforeach; endif; ?>
							</td>
							<td><a href="<?=site_url();?>admin/mahana/thread/<?=$thread['thread_id']?>/<?=$thread['messages'][0]['sender_id'];?>">View thread</a></td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				
				<?php //$this->load->view('admin/partials/pagination', $pagination); ?>
			<?php else: ?>
				<div class="no_data"><?php echo lang('mahana:no_threads'); ?></div>
			<?php endif;?>
			
		</div>
	</section>
</div>