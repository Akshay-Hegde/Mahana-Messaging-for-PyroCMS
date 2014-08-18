<div id="content-body">
	<section class="title">
		<h4><?=lang('mahana:thread'); ?> <?=$id;?>: <?=$subject;?></h4>
	</section>
	
	<section class="item">
		<div class="content">
			<?php if (!empty($thread) && !$thread['err']): ?>
				<?php foreach($thread['retval'] as $msg) : ?>
				<h4><?=$msg['display_name'];?> (<?=$msg['cdate'];?>):</h4>
				<p><?=$msg['body'];?></p>
				
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</section>
</div>