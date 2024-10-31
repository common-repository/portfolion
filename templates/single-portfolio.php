<?php get_header(); ?>
	<h1><?php the_title(); ?></h1>
	<?php while(have_posts()) : the_post(); ?>
		<article id="<?php the_ID(); ?>">
			<?php if(has_post_thumbnail()): ?>
				<?php the_post_thumbnail('full'); ?>
			<?php endif; ?>
			<?php if(has_the_project_link()): ?>
				<a href="<?php the_project_link(); ?>" target="_blank"><?php the_project_link(); ?></a>
			<?php endif; ?>
			<?php the_content(); ?>
			<?php edit_post_link('Edit Project');Ê?>
		</article>
	<?php endwhile; ?>
<?php get_footer(); ?>