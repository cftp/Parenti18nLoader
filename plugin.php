n Name: (#113391) Parent Theme i18n Autoloader
 * Description: Load Twenty12 Child theme translation files automagically from Parent
 */

add_action( 'muplugins_loaded', array( 'WPSE113391Parenti18nLoader', 'getInstance' ) );
class WPSE113391Parenti18nLoader
{
    public static $instance = null;

    private $theme = null;

    public static function getInstance()
    {
        null === self::$instance AND self::$instance = new self;
        return self::$instance;
    }

    public function __construct()
    {
        add_action( 'after_setup_theme', array( $this, 'i18nAutoloader' ), 20 );
    }

    public function setTheme( $theme )
    {
        return $this->theme = $theme;
    }

    public function getTheme()
    {
        return $this->theme;
    }

    public function i18nAutoloader()
    {
        if ( ! is_child_theme() )
            return;

        $current_theme = wp_get_theme();
        if ( '' === $current_theme->parent()->get( 'DomainPath' ) )
        {
            $this->setTheme( $current_theme->parent() );
            add_filter( 'override_load_textdomain', array( $this, 'overrideI18nLoader' ), 10, 3 );
        }
        $current_theme->parent()->load_textdomain();
    }

    public function overrideI18nLoader( $activate, $domain, $mofile )
    {
        // Don't intercept anything else: Self removing
        remove_filter( current_filter(), __FUNCTION__ );

        // Rebuild the internals of WP_Theme::get_stylesheet_directory() and load_theme_textdomain()
        $theme  = $this->getTheme();
        $path   = trailingslashit( $theme->get_theme_root() ).$theme->get_template();
        $locale = apply_filters( 'theme_locale', get_locale(), $domain );

        load_textdomain( $domain, "{$path}/{$locale}.mo" );

        // Return true to abort further attempts
        return true;
    }
}