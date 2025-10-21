<?php

namespace WP_CLI_Login;

use UnexpectedValueException;
use WP_CLI_Login\WP_CLI_Login_Server;

class ServerPlugin
{
    /**
     * Plugin file path, relative to plugins directory.
     */
    const PLUGIN_FILE = 'wp-cli-login-server/wp-cli-login-server.php';

    /**
     * Absolute path to primary plugin file.
     * @var
     */
    private $file;

    /**
     * ServerPlugin constructor.
     *
     * @param $file
     */
    public function __construct($file)
    {
        $this->file = $file;
    }

    /**
     * Check if the plugin is currently active.
     *
     * @return bool
     */
    public static function isActive()
    {
        return class_exists(WP_CLI_Login_Server::class, false);
    }

    /**
     * Get a new instance for the installed plugin.
     *
     * @return static
     */
    public static function installed()
    {
        $must_use = static::mustUse();

        if ($must_use->exists()) {
            return $must_use;
        }

        return new static(WP_PLUGIN_DIR . '/' . static::PLUGIN_FILE);
    }

    /**
     * @return MustUseServerPlugin
     */
    public static function mustUse()
    {
        return new MustUseServerPlugin(WPMU_PLUGIN_DIR . '/' . basename(static::PLUGIN_FILE));
    }

    /**
     * Try to see if the plugin was installed with Composer or not.
     *
     * Checks if there is a composer.json adjacent to the main plugin file.
     * This file will not exist when installed by the command.
     *
     * @return bool
     */
    public function isComposerInstalled()
    {
        return file_exists(dirname($this->file) . '/composer.json');
    }

    /**
     * Check if the main plugin file exists.
     *
     * @return bool
     */
    public function exists()
    {
        return file_exists($this->file);
    }

    /**
     * Get the absolute path to the main plugin file.
     *
     * @return mixed
     */
    public function fullPath()
    {
        return $this->file;
    }

    /**
     * Get the plugin name, as defined in the header.
     *
     * @return mixed
     */
    public function name()
    {
        return $this->data()['Name'];
    }

    /**
     * Get the plugin version, as defined in the header.
     *
     * @return mixed
     */
    public function version()
    {
        return $this->data()['Version'];
    }

    /**
     * Check if the plugin's version satisfies the given constraints.
     *
     * @param string $constraints  Composer Semver-style constraints
     *
     * @return bool
     */
    public function versionSatisfies($constraints)
    {
        $constraints = is_string($constraints) ? trim($constraints) : '';
        $version = is_string($this->version()) ? trim($this->version()) : '';

        if ($constraints === '' || $version === '') {
            // Saved installs prior to 1.5 did not persist a constraint; force reset flow.
            return false;
        }

        try {
            $semver = 'Composer\\Semver\\Semver';

            if (!class_exists($semver)) {
                return false;
            }

            return call_user_func([$semver, 'satisfies'], $version, $constraints);
        } catch (UnexpectedValueException $exception) {
            // Bail gracefully if either side is not a valid semver string.
            return false;
        }
    }

    /**
     * Get the plugin file header data.
     *
     * @return array
     */
    public function data()
    {
        static $loaded;

        if (!$loaded) {
            $loaded = get_plugin_data($this->file);
        }

        return $loaded;
    }
}
