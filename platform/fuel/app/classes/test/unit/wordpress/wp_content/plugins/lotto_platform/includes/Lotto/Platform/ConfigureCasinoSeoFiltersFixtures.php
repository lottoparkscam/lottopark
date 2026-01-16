<?php

namespace Test\Unit\Wordpress\Wp_content\Plugins\Lotto_platform\Includes\Lotto\Platform;

final class ConfigureCasinoSeoFiltersFixtures
{
    public static function build(string $homeUrl): void
    {
        eval("function remove_filter(string \$tag, string \$function_to_remove): void { // NOTE: snake to match mocked method
            \Test_Unit::assertSame('template_redirect', \$tag);
            \Test_Unit::assertSame('redirect_canonical', \$function_to_remove);
        }");
        eval("function add_action(string \$tag, string \$function_to_add): void { // NOTE: snake to match mocked method
            \Test_Unit::assertSame('wp_loaded', \$tag);
            \Test_Unit::assertSame('redirect_canonical', \$function_to_add);
        }");
        eval('function add_filter(string $tag, $function_to_add, int $priority = 10, int $accepted_args = 1): void { // NOTE: snake to match mocked method
            Test_Unit::assertSame(10, $priority);
            Test_Unit::assertTrue(is_callable($function_to_add));
            switch ($tag) {
                case \'wpseo_title\':
                case \'wpseo_metadesc\':
                case \'wpseo_opengraph_title\':
                case \'wpseo_opengraph_desc\':
                    Test_Unit::assertSame(2, $accepted_args);
                    return;
                case \'wpseo_opengraph_url\':
                case \'wpseo_canonical\': 
                    Test_Unit::assertSame(1, $accepted_args);
                    return;
            }
            throw new Exception(\'unexpected tag\');
        }');
        eval("function get_home_url(): string { // NOTE: snake to match mocked method
            return \"$homeUrl\";
        }");
        eval("function url_to_postid(string \$url): int { // NOTE: snake to match mocked method
            return 0;
        }");
        eval("function get_post_meta(int \$postId, string \$key, bool \$single): string { // NOTE: snake to match mocked method
            AssertTrue(\$single);
            return \"dummy meta\";
        }");
        if (!class_exists('Yoast\WP\SEO\Presentations\Indexable_Post_Type_Presentation')) {
            eval('namespace Yoast\WP\SEO\Presentations;
            
            class Model {
                public string $permalink;
            }
            class Indexable_Post_Type_Presentation {
                public Model $model;
            }');
        }
    }
}