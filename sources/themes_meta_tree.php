<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    core_themeing
 */

/**
 * Sitemap node type base class.
 *
 * @package        core_themeing
 */
class Meta_tree_builder
{
    private $theme;
    private $addons;

    /**
     * Constructor.
     *
     * @param  ID_TEXT $theme The theme
     */
    public function __construct($theme)
    {
        $this->theme = $theme;
        $this->addons = array_keys(find_all_addons());
    }

    /**
     * Build out meta-directories for a theme. (Sym-links etc).
     */
    public function refresh()
    {
        require_code('files');

        $theme_dir = get_custom_file_base() . '/themes/' . $theme;
        if (!file_exists($theme_dir)) {
            mkdir($theme_dir, 0777);
            fix_permissions($theme_dir);
        }
        $meta_dir = $theme_dir . '/_meta_dir';

        if (is_dir($meta_dir)) {
            deldir_contents($meta_dir);
        } else {
            mkdir($meta_dir, 0777);
            fix_permissions($meta_dir);

            $this->put_in_standard_dir_files($meta_dir);
        }

        $meta_dirs_to_build = array(
            'screens',

            'templates',
            'css',
            'javascript',
            'xml',
            'text',

            'templates-related',
            'css-related',
            'javascript-related',
            'xml-related',
            'text-related',
        );
        foreach ($meta_dirs_to_build as $meta_dir_to_build) {
            mkdir($meta_dir . '/' . $meta_dir_to_build, 0777);
            fix_permissions($meta_dir . '/' . $meta_dir_to_build);

            switch ($meta_dir_to_build) {
                case 'screens':
                    TODO
                    break;

                case 'templates',
                case 'css',
                case 'javascript',
                case 'xml',
                case 'text',
                    $this->put_in_addon_tree($meta_dir, $meta_dir_to_build);
                    break;

                case 'templates-related',
                case 'css-related',
                case 'javascript-related',
                case 'xml-related',
                case 'text-related',
                    // TODO: Requires different logic
                    $this->put_in_addon_tree($meta_dir, $meta_dir_to_build);
                    break;
            }
        }
    }

    /**
     * Put in an addon hierarchy under a path.
     *
     * @param  PATH $path The path
     * @param  ID_TEXT $subdir The theme subdirectory we're working against
     */
    private function put_in_addon_tree($path, $subdir)
    {
        $_all_path = $path . '/_all';
        mkdir($_all_path, 0777);
        fix_permissions($_all_path);

        foreach ($this->addons as $addon) {
            $_path = $path . '/' . $addon;
            mkdir($_path, 0777);
            fix_permissions($_path);

            $files = find_theme_files_from_addon($addon, $subdir);
            foreach ($files as $file) {
                symlink($file['full_path'], $_path . '/' . $file['stub']);

                symlink($file['full_path'], $_all_path . '/' . $file['stub']);
            }
        }
    }

    /**
     * Find all of a particular kind of file in an addon.
     *
     * @param  ID_TEXT $addon The addon
     * @param  ID_TEXT $subdir The theme subdirectory we're working against
     * @return array The files
     */
    private function find_theme_files_from_addon($addon, $subdir)
    {
        static $cache = array();
        if (isset($cache[$addon][$subdir])) {
            return $cache[$addon][$subdir];
        }

        $files = array();

        require_code('hooks/systems/addon_registry/' . $addon);
        $ob = object_factory('Hook_addon_registry_' . $addon);
        $_files = $ob->get_file_list();
        $test_for = 'themes/default/' . $subdir . '/';
        foreach ($_files as $file) {
            if (substr($file, 0, strlen($test_for)) == $test_for) {
                if ((basename($file) != 'index.html') && (basename($file) != '.htaccess')) {
                    $files[] = array(
                        'full_path' => get_file_base() . '/' . $file, // TODO: Should be override path
                        'stub' => substr($file, strlen($test_for)),
                    );
                }
            }
        }

        $cache[$addon][$subdir] = $files;

        return $files;
    }

    /**
     * Put standard directory files (security files) into a directory.
     *
     * @param  PATH $path The path
     */
    private function put_in_standard_dir_files($path)
    {
        copy(get_custom_file_base() . '/themes/default/templates/index.html', $path . '/index.html');
        fix_permissions($meta_dir . '/index.html');

        copy(get_custom_file_base() . '/themes/default/templates/.htaccess', $path . '/.htaccess');
        fix_permissions($meta_dir . '/.htaccess');
    }
}
