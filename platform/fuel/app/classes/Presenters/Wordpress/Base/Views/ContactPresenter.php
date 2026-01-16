<?php

namespace Presenters\Wordpress\Base\Views;

use Presenters\Wordpress\AbstractWordpressPresenter;
use Container;
use Models\Whitelabel;
use Presenters\Wordpress\Base\Views\Partials\ContactFormPresenter;

class ContactPresenter extends AbstractWordpressPresenter
{
    private Whitelabel $whitelabel;
    private ContactFormPresenter $contactFormPresenter;

    public function __construct(ContactFormPresenter $contactFormPresenter)
    {
        $this->contactFormPresenter = $contactFormPresenter;
        $this->whitelabel = Container::get('whitelabel');
    }

    public function view(): string
    {
        global $post;

        $content = get_extended($post->post_content);
        $content['extended'] = str_replace("<a", '<br><a', $content['extended']);

        $view = $this->forge([
            'contactForm' => $this->contactFormPresenter->view(),
            'mainContent' => $content['main'],
            'companyDetails' => $content['extended'],
            'company' => $this->whitelabel->companyDetails,
        ]);

        remove_filter('the_content', 'wpautop');
        return apply_filters('the_content', $view);
    }
}
