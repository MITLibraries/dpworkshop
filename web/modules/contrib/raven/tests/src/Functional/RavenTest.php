<?php

namespace Drupal\Tests\raven\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests Raven module.
 *
 * @group raven
 */
class RavenTest extends BrowserTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = ['raven', 'raven_test'];

  /**
   * Tests Raven module configuration UI and hooks.
   */
  public function testRavenConfigAndHooks() {
    $admin_user = $this->drupalCreateUser(['administer site configuration']);
    $this->drupalLogin($admin_user);
    $config['client_key'] = 'https://user:password@sentry.test/123456';
    $config['fatal_error_handler'] = 1;
    foreach (range(1, 8) as $level) {
      $config["log_levels[$level]"] = '1';
    }
    $this->drupalPostForm('admin/config/development/raven', $config, t('Save configuration'));
    $this->assertSession()->responseHeaderEquals('X-Logged', 'Logged');
    $this->assertSession()->responseHeaderEquals('X-Not-Logged', NULL);
    $this->assertSession()->responseHeaderEquals('X-Stacktrace-File', drupal_get_path('module', 'raven_test') . '/raven_test.module');

    // Test fatal error handling.
    $memory_limit = mt_rand(16000000, 17999999);
    $url = $admin_user->toUrl()->setOption('query', ['memory_limit' => $memory_limit]);
    // Output should be the memory limit and 0 pending events/requests.
    $this->assertEqual($memory_limit . '00', $this->drupalGet($url));
  }

}