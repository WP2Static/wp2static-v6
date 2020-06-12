<?php

namespace StaticHTMLOutput;

class CrawlLog {

    public static function createTable() : void {
        global $wpdb;

        $table_name = $wpdb->prefix . 'statichtmloutput_crawl_log';

        $charset_collate = $wpdb->get_charset_collate();

        /**
         * Detected/discovered URLs added with initial status of 0
         * and will be updated with response code after crawling
         */
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            url VARCHAR(2083) NOT NULL,
            note TEXT NOT NULL,
            status SMALLINT DEFAULT 0 NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    /**
     * Add all Urls to log
     *
     * @param string[] $urls List of URLs to log info for
     */
    public static function addUrls( array $urls, string $note, int $status = 0 ) : void {
        global $wpdb;

        $table_name = $wpdb->prefix . 'statichtmloutput_crawl_log';

        $placeholders = [];
        $values = [];

        foreach ( $urls as $url ) {
            if ( ! $url ) {
                continue;
            }

            $placeholders[] = '(%s, %s, %d)';
            $values[] = rawurldecode( $url );
            $values[] = $note;
            $values[] = $status;
        }

        $query_string =
            'INSERT INTO ' . $table_name . ' (url, note, status) VALUES ' .
            implode( ', ', $placeholders );
        $query = $wpdb->prepare( $query_string, $values );

        $wpdb->query( $query );
    }

    /**
     *  Get all crawlable URLs
     *
     *  @return string[] All crawlable URLs
     */
    public static function getCrawlablePaths() : array {
        global $wpdb;
        $urls = [];

        $table_name = $wpdb->prefix . 'statichtmloutput_crawl_log';

        $rows = $wpdb->get_results( "SELECT url FROM $table_name ORDER by url ASC" );

        foreach ( $rows as $row ) {
            $urls[] = $row->url;
        }

        return $urls;
    }

    /**
     *  Get total crawlable URLs
     *
     *  @return int Total crawlable URLs
     */
    public static function getTotalCrawlableURLs() : int {
        global $wpdb;

        $table_name = $wpdb->prefix . 'statichtmloutput_crawl_log';

        $total_crawl_log = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );

        return $total_crawl_log;
    }

    /**
     *  Clear CrawlCrawl Log via truncate or deletion
     */
    public static function truncate() : void {
        Logger::l( 'Deleting CrawlCrawl Log' );

        global $wpdb;

        $table_name = $wpdb->prefix . 'statichtmloutput_crawl_log';

        $wpdb->query( "TRUNCATE TABLE $table_name" );

        $total_crawl_log = self::getTotalCrawlableURLs();

        if ( $total_crawl_log > 0 ) {
            Logger::l( 'failed to truncate CrawlCrawl Log: try deleting instead' );
        }
    }

    /**
     *  Count URLs in Crawl Log
     */
    public static function getTotal() : int {
        global $wpdb;

        $table_name = $wpdb->prefix . 'statichtmloutput_crawl_log';

        $total = $wpdb->get_var( "SELECT count(*) FROM $table_name" );

        return $total;
    }

    /**
     *  Update URL status
     */
    public static function updateStatus( string $url, int $status) : void {
        global $wpdb;

        $table_name = $wpdb->prefix . 'statichtmloutput_crawl_log';

        error_log("updating status $status $url" . PHP_EOL);

        $wpdb->update(
            $table_name,
            [ 'status' => $status ],
            [ 'url' => $url ]
        );
    }
}
