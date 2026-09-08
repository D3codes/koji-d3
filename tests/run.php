<?php
// Mount tests at /wordpress/d3-tests in a disposable Playground installation.
require dirname( __DIR__ ) . '/wp-load.php';
header( 'Content-Type: text/plain' );
try {
	foreach ( new RecursiveIteratorIterator( new RecursiveDirectoryIterator( get_stylesheet_directory() ) ) as $file ) {
		if ( 'php' === $file->getExtension() ) { token_get_all( file_get_contents( $file->getPathname() ), TOKEN_PARSE ); }
	}
	echo "PASS: PHP syntax for every child-theme PHP file\n";
	require __DIR__ . '/links.php';
} catch ( Throwable $error ) {
	http_response_code( 500 );
	echo $error->getMessage();
}
