<?php
/**
 * @package Clark University Sentry Error Monitoring
 * @version 1.0.0
 */

/*
Plugin Name: Sentry Error Monitoring
Plugin URI:
Description: Sends errors to Sentry (https://sentry.io/)
Author: Mark Pemburn (mpemburn@clarku.edu)
Version: 1.0.0
Author URI:
*/

namespace ClarkU_Sentry_Monitoring;

require_once __DIR__ . '/vendor/autoload.php';

define('SENTRY_MONITORING_ROOT_FILE', __FILE__);

use ClarkU_Sentry_Monitoring\SentryMonitoringAdminPage;

SentryMonitoringAdminPage::boot();