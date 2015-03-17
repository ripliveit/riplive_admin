CREATE OR REPLACE DEFINER=`riplive_wp`@`localhost` VIEW wp_seo_view AS
	SELECT CONCAT('/authors/', users.user_nicename) AS path, users.display_name AS title, 

		(SELECT usermeta.meta_value 
		 FROM wp_usermeta AS usermeta 
		 WHERE usermeta.meta_key = 'description' 
		 AND users.id = usermeta.user_id) AS description,

		(SELECT posts.guid
		FROM wp_usermeta AS usermeta, wp_posts AS posts
		WHERE meta_key = 'wp_user_avatar'
		AND meta_value = posts.ID
		AND users.id = usermeta.user_id) AS image,
		
		'0.2' AS priority, 'monthly' AS frequency
	FROM wp_users AS users

	UNION

	SELECT  CONCAT('/artists/', post_name) AS path, posts.post_title AS title, posts.post_content AS description, 
	(SELECT guid FROM wp_posts WHERE ID = meta.meta_value ) AS image, '0.5' AS priority, 'weekly' AS frequency
	FROM wp_posts AS posts, wp_postmeta AS meta
	WHERE posts.post_type =  'artists'
	AND posts.post_status =  'publish'
	AND posts.id = meta.post_id
	AND meta.meta_key =  '_thumbnail_id'

	UNION

	SELECT CONCAT('/artists/genre/', terms.slug) AS path, terms.name AS title,
	 CONCAT('Tutti le artisti di riplive.it di genere ', terms.name) AS description, 'http://www.riplive.it/images/logo_medium.jpg' AS image,
	 '0.5' AS priority, 'weekly' AS frequency
	FROM wp_terms AS terms, wp_term_taxonomy AS term_taxonomy
	WHERE term_taxonomy.term_id = terms.term_id
	AND term_taxonomy.taxonomy = 'artist-genre'

	UNION

	SELECT CONCAT('/artists/tag/', terms.slug) AS path, terms.name AS title, 
	CONCAT('Tutti gli artisti di riplive.it taggati con ', terms.name) AS description, 'http://www.riplive.it/images/logo_medium.jpg' AS image,
	'0.5' AS priority, 'weekly' AS frequency
	FROM wp_terms AS terms, wp_term_taxonomy AS term_taxonomy
	WHERE term_taxonomy.term_id = terms.term_id
	AND term_taxonomy.taxonomy = 'artist-tag'

	UNION

	SELECT CONCAT('/categories/', terms.slug) AS path, terms.name AS title, 
	CONCAT('Tutti gli articoli di riplive.it che hanno categoria ', terms.name) AS description, 'http://www.riplive.it/images/logo_medium.jpg' AS image,
	'0.7' AS priority, 'daily' AS frequency
	FROM wp_terms AS terms, wp_term_taxonomy AS term_taxonomy
	WHERE term_taxonomy.term_id = terms.term_id
	AND term_taxonomy.taxonomy = 'category'

	UNION

	SELECT CONCAT('/charts/', charts_archive.chart_archive_slug) AS path, posts.post_title AS title, posts.post_content AS description, 
	(SELECT guid FROM wp_posts WHERE ID = meta.meta_value ) AS image, '0.5' AS priority, 'weekly' AS frequency
	FROM wp_charts_archive AS charts_archive, wp_posts AS posts, wp_postmeta AS meta
	WHERE charts_archive.id_chart = posts.ID
	AND posts.ID = meta.post_id
	AND meta.meta_key =  '_thumbnail_id'

	UNION

	SELECT  CONCAT('/news/', post_name) AS path, posts.post_title AS title, posts.post_content AS description, 
	(SELECT guid FROM wp_posts WHERE ID = meta.meta_value ) AS image, '1' AS priority, 'daily' AS frequency
	FROM wp_posts AS posts, wp_postmeta AS meta
	WHERE posts.post_type =  'post'
	AND posts.post_status =  'publish'
	AND posts.id = meta.post_id
	AND meta.meta_key =  '_thumbnail_id'

	UNION

	SELECT CONCAT('/podcasts/', posts.post_name, '/', podcasts.id) AS path, podcasts.title, summary AS description, 

		(SELECT posts.guid 
		FROM wp_posts 
		WHERE posts.ID = meta.meta_value) AS image, 
		'1' AS priority, 'daily' AS frequency
		
	FROM wp_podcasts AS podcasts, wp_posts AS posts, wp_postmeta AS meta
	WHERE podcasts.id_program = posts.ID
	AND posts.ID = meta.post_id
	AND meta.meta_key =  '_thumbnail_id'

	UNION

	SELECT  CONCAT('/programs/', post_name) AS path, posts.post_title AS title, posts.post_content AS description, 
	(SELECT guid FROM wp_posts WHERE ID = meta.meta_value ) AS image, '0.2' AS priority, 'monthly' AS frequency
	FROM wp_posts AS posts, wp_postmeta AS meta
	WHERE posts.post_type =  'programs'
	AND posts.post_status =  'publish'
	AND posts.ID = meta.post_id
	AND meta.meta_key =  '_thumbnail_id'

	UNION

	SELECT  CONCAT('/songs/', post_name) AS path, posts.post_title AS title, posts.post_content AS description, 
	(SELECT guid FROM wp_posts WHERE ID = meta.meta_value ) AS image, '0.5' AS priority, 'weekly' AS frequency
	FROM wp_posts AS posts, wp_postmeta AS meta
	WHERE posts.post_type =  'songs'
	AND posts.post_status =  'publish'
	AND posts.id = meta.post_id
	AND meta.meta_key =  '_thumbnail_id'

	UNION

	SELECT CONCAT('/songs/genre/', terms.slug) AS path, terms.name AS title, 
	CONCAT('Tutti le canzoni di riplive.it con genere ', terms.name) AS description, 'http://www.riplive.it/images/logo_medium.jpg' AS image,
	'0.5' AS priority, 'weekly' AS frequency
	FROM wp_terms AS terms, wp_term_taxonomy AS term_taxonomy
	WHERE term_taxonomy.term_id = terms.term_id
	AND term_taxonomy.taxonomy = 'song-genre'

	UNION

	SELECT CONCAT('/songs/tag/', terms.slug) AS path, terms.name AS title, 
	CONCAT('Tutti le canzoni di riplive.it che hanno tag ', terms.name) AS description, 'http://www.riplive.it/images/logo_medium.jpg' AS image, 
	'0.5' AS priority, 'weekly' AS frequency
	FROM wp_terms AS terms, wp_term_taxonomy AS term_taxonomy
	WHERE term_taxonomy.term_id = terms.term_id
	AND term_taxonomy.taxonomy = 'song-tag'

	UNION

	SELECT CONCAT('/tags/', terms.slug) AS path, terms.name AS title, 
	CONCAT('Tutti gli articoli di riplive.it che hanno tag ', terms.name) AS description, 'http://www.riplive.it/images/logo_medium.jpg' AS image,
	'0.7' AS priority, 'daily' AS frequency
	FROM wp_terms AS terms, wp_term_taxonomy AS term_taxonomy
	WHERE term_taxonomy.term_id = terms.term_id
	AND term_taxonomy.taxonomy = 'post_tag'
