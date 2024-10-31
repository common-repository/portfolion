<?php get_header(); ?>
	<h1>Portfolio Tag <i><?php single_tag_title(); ?></i>.</h1>
	<?php while(have_posts()) : the_post(); ?>
		<article id="<?php the_ID(); ?>">
			<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
			<?php if(has_post_thumbnail()): ?>
				<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail(); ?></a>
			<?php endif; ?>
			<?php if(has_the_project_link()): ?>
				<a href="<?php the_project_link(); ?>" target="_blank"><?php the_project_link(); ?></a>
			<?php endif; ?>
			<?php the_content(); ?>
			<?php edit_post_link('Edit Project');Ê?>
		</article>
	<?php endwhile; ?>
	<?php get_sidebar(); ?>
<?php get_footer(); ?>