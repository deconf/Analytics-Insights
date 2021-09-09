<?php
session_start();
if ( $_GET['state'] && $_GET['code'] ) {
	$redirect_uri = esc_url_raw( $_GET['state'] . '&aiwp_access_code=' . $_GET['code'] );
	header( 'Location: ' . filter_var( $redirect_uri, FILTER_SANITIZE_URL ) );
}