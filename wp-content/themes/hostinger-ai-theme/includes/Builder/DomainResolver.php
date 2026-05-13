<?php

namespace Hostinger\AiTheme\Builder;

use Hostinger\WpHelper\Utils as Helper;

defined( 'ABSPATH' ) || exit;

class DomainResolver {
    private Helper $helper;

    public function __construct( Helper $helper ) {
        $this->helper = $helper;
    }

    public function get_current_domain(): string {
        if ( method_exists( $this->helper, 'isPreviewDomain' ) && method_exists( $this->helper, 'getSiteUrlFromDb' ) ) {
            if ( $this->helper->isPreviewDomain() ) {
                $site_url = $this->helper->getSiteUrlFromDb();
                if ( ! empty( $site_url ) ) {
                    return preg_replace( '/^www\./', '', $site_url );
                }
            }
        }

        $host = $this->helper->getHostInfo();

        return preg_replace( '/^www\./', '', (string) $host );
    }
}
