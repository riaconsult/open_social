<?php

namespace Drupal\social_user\Plugin\Block;

use Drupal\activity_creator\ActivityNotifications;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'AccountHeaderBlock' block.
 *
 * @Block(
 *   id = "account_header_block",
 *   admin_label = @Translation("Account header block"),
 *   context = {
 *     "user" = @ContextDefinition("entity:user")
 *   }
 * )
 */
class AccountHeaderBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The activity notifications.
   *
   * @var \Drupal\activity_creator\ActivityNotifications
   */
  protected $activityNotifications;

  /**
   * The Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * AccountHeaderBlock constructor.
   *
   * @param array $configuration
   *   The configuration.
   * @param string $plugin_id
   *   The plugin id.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\activity_creator\ActivityNotifications $activity_notifications
   *   The activity creator, activity notifications.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The Entity Type Manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The Config Factory.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ModuleHandlerInterface $module_handler, RendererInterface $renderer, ActivityNotifications $activity_notifications, EntityTypeManagerInterface $entityTypeManager, ConfigFactoryInterface $configFactory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->moduleHandler = $module_handler;
    $this->renderer = $renderer;
    $this->activityNotifications = $activity_notifications;
    $this->entityTypeManager = $entityTypeManager;
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('module_handler'),
      $container->get('renderer'),
      $container->get('activity_creator.activity_notifications'),
      $container->get('entity_type.manager'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // TODO: Remove hook_social_user_account_header_links as it's replaced by menu specific hooks.

    $block = [
      '#attributes' => [
        'class' => ['navbar-user'],
      ],
      'menu_items' => [
        '#theme' => 'item_list',
        '#list_type' => 'ul',
        '#attributes' => [
          'class' => ['nav', 'navbar-nav'],
        ],
        '#items' => [],
      ],
    ];

    // Create a convenience shortcut for later code.
    $menu_items = &$block['menu_items']['#items'];

    /** @var \Drupal\Core\Session\AccountInterface $account */
    $account = $this->getContextValue('user');

    if ($account->isAuthenticated()) {
      $menu_items['create'] = [
        '#type' => 'account_header_element',
        '#title' => $this->t('Create New Content'),
        '#url' => Url::fromRoute('<none>'),
        '#icon' => 'add_box',
        '#label' => $this->t('New Content'),
        'add_event' => [
          '#type' => 'link',
          '#attributes' => [
            'title' => $this->t('Create New Event'),
          ],
          '#url' => Url::fromRoute('node.add', ['node_type' => 'event']),
          '#title' => $this->t('New Event')
        ],
        'add_topic' => [
          '#type' => 'link',
          '#attributes' => [
            'title' => $this->t('Create New Topic'),
          ],
          '#url' => Url::fromRoute('node.add', ['node_type' => 'topic']),
          '#title' => $this->t('New Topic')
        ],
        'add_group' => [
          '#type' => 'link',
          '#attributes' => [
            'title' => $this->t('Create New Group'),
          ],
          '#url' => Url::fromRoute('entity.group.add_page'),
          '#title' => $this->t('New Group')
        ],
      ];

      // Weights can be used to order the subitems of an account_header_element.
      // TODO: Invoke hook_social_user_account_header_create_links()

      // TODO: Invoke hook_social_user_account_header_create_links_alter().

      $account_name = $account->getDisplayName();

      // TODO: account_box requires 'profile' class
      $menu_items['account_box'] = [
        '#type' => 'account_header_element',
        '#wrapper_attributes' => [
          'class' => ['profile'],
        ],
        '#icon' => 'account_circle',
        '#title' => $this->t('Profile of @account', ['@account' => $account_name]),
        '#label' => $account_name,
        '#url' => Url::fromRoute('<none>'),
        'signed_in_as' => [
          '#wrapper_attributes' => [
            'class' => [ 'dropdown-header', 'header-nav-current-user' ],
          ],
          '#type' => 'inline_template',
          '#template' => '{{ tagline }} <strong class="text-truncate">{{ object }} </strong>',
          '#context' => [
            'tagline' => $this->t('Signed in as'),
            'object'  => $account_name,
          ],
        ],
//        'divide_mobile' => [
//          'divider' => 'true',
//          'classes' => 'divider mobile',
//          'attributes' => 'role=separator',
//        ],
//        'messages_mobile' => [],
//        'notification_mobile' => [],
//        'divide_profile' => [
//          'divider' => 'true',
//          'classes' => 'divider',
//          'attributes' => 'role=separator',
//        ],
//        'my_profile' => [
//          'classes' => '',
//          'link_attributes' => '',
//          'link_classes' => '',
//          'icon_classes' => '',
//          'icon_label' => '',
//          'title' => $this->t('View my profile'),
//          'label' => $this->t('My profile'),
//          'title_classes' => '',
//          'url' => Url::fromRoute('user.page'),
//        ],
//        'my_events' => [
//          'classes' => '',
//          'link_attributes' => '',
//          'link_classes' => '',
//          'icon_classes' => '',
//          'icon_label' => '',
//          'title' => $this->t('View my events'),
//          'label' => $this->t('My events'),
//          'title_classes' => '',
//          'url' => Url::fromRoute('view.events.events_overview', [
//            'user' => $account->id(),
//          ]),
//        ],
//        'my_topics' => [
//          'classes' => '',
//          'link_attributes' => '',
//          'link_classes' => '',
//          'icon_classes' => '',
//          'icon_label' => '',
//          'title' => $this->t('View my topics'),
//          'label' => $this->t('My topics'),
//          'title_classes' => '',
//          'url' => Url::fromRoute('view.topics.page_profile', [
//            'user' => $account->id(),
//          ]),
//        ],
//        'my_groups' => [
//          'classes' => '',
//          'link_attributes' => '',
//          'link_classes' => '',
//          'icon_classes' => '',
//          'icon_label' => '',
//          'title' => $this->t('View my groups'),
//          'label' => $this->t('My groups'),
//          'title_classes' => '',
//          'url' => Url::fromRoute('view.groups.page_user_groups', [
//            'user' => $account->id(),
//          ]),
//        ],
//        'divide_content' => [
//          'divider' => 'true',
//          'classes' => 'divider',
//          'attributes' => 'role=separator',
//        ],
//        'my_content' => [
//          'classes' => '',
//          'link_attributes' => '',
//          'link_classes' => '',
//          'icon_classes' => '',
//          'icon_label' => '',
//          'title' => $this->t("View content I'm following"),
//          'label' => $this->t('Following'),
//          'title_classes' => '',
//          'url' => Url::fromRoute('view.following.following'),
//        ],
//        'divide_account' => [
//          'divider' => 'true',
//          'classes' => 'divider',
//          'attributes' => 'role=separator',
//        ],
//        'my_account' => [
//          'classes' => '',
//          'link_attributes' => '',
//          'link_classes' => '',
//          'icon_classes' => '',
//          'icon_label' => '',
//          'title' => $this->t('Settings'),
//          'label' => $this->t('Settings'),
//          'title_classes' => '',
//          'url' => Url::fromRoute('entity.user.edit_form', [
//            'user' => $account->id(),
//          ]),
//        ],
//        'edit_profile' => [
//          'classes' => '',
//          'link_attributes' => '',
//          'link_classes' => '',
//          'icon_classes' => '',
//          'icon_label' => '',
//          'title' => $this->t('Edit profile'),
//          'label' => $this->t('Edit profile'),
//          'title_classes' => '',
//          'url' => Url::fromRoute('entity.profile.type.user_profile_form', [
//            'user' => $account->id(),
//            'profile_type' => 'profile',
//          ]),
//          'access' => $account->hasPermission('add own profile profile') || $account->hasPermission('bypass profile access'),
//        ],
//        'divide_logout' => [
//          'divider' => 'true',
//          'classes' => 'divider',
//          'attributes' => 'role=separator',
//        ],
//        'logout' => [
//          'classes' => '',
//          'link_attributes' => '',
//          'link_classes' => '',
//          'icon_classes' => '',
//          'icon_label' => '',
//          'title' => $this->t('Logout'),
//          'label' => $this->t('Logout'),
//          'title_classes' => '',
//          'url' => Url::fromRoute('user.logout'),
//        ],
      ];

      // Weights can be used to order the subitems of an account_header_element.
      // TODO: Invoke hook_social_user_account_header_account_links()

      // TODO: Invoke hook_social_user_account_header_account_links_alter().
    }

    // TODO: Implement dependency injection here.
    // We allow modules to add their items to the account header block.
    // We use the union operator (+) to ensure modules can't overwrite items
    // defined above. They should use the alter hook for that.
    $context = $this->getContextValues();
    $menu_items += \Drupal::moduleHandler()->invokeAll('social_user_account_header_items', [$context]);

    // Allow modules to alter the defined account header block items.
    \Drupal::moduleHandler()->alter('social_user_account_header_items', $menu_items, $context);

    return $block;

    if ($account->id() !== 0) {
      // Check if the current user is allowed to create new books.
      if ($this->moduleHandler->moduleExists('social_book')) {
        $links['add']['below']['add_book'] = [
          'classes' => '',
          'link_attributes' => '',
          'link_classes' => '',
          'icon_classes' => '',
          'icon_label' => '',
          'title' => $this->t('Create New Book page'),
          'label' => $this->t('New book page'),
          'title_classes' => '',
          'url' => Url::fromRoute('node.add', [
            'node_type' => 'book',
          ]),
          'access' => $account->hasPermission('create new books'),
        ];
      }

      // Check if the current user is allowed to create new pages.
      if ($this->moduleHandler->moduleExists('social_page')) {
        $links['add']['below']['add_page'] = [
          'classes' => '',
          'link_attributes' => '',
          'link_classes' => '',
          'icon_classes' => '',
          'icon_label' => '',
          'title' => $this->t('Create New Page'),
          'label' => $this->t('New page'),
          'title_classes' => '',
          'url' => Url::fromRoute('node.add', [
            'node_type' => 'page',
          ]),
        ];
      }

      // Check if the current user is allowed to create new landing pages.
      if ($this->moduleHandler->moduleExists('social_landing_page')) {
        $links['add']['below']['add_landing_page'] = [
          'classes' => '',
          'link_attributes' => '',
          'link_classes' => '',
          'icon_classes' => '',
          'icon_label' => '',
          'title' => $this->t('Create New Landing Page'),
          'label' => $this->t('New landing page'),
          'title_classes' => '',
          'url' => Url::fromRoute('node.add', [
            'node_type' => 'landing_page',
          ]),
        ];
      }


      if ($this->moduleHandler->moduleExists('social_private_message')) {
        if ($navigation_settings_config->get('display_social_private_message_icon') === 1) {
          // Fetch the amount of unread items.
          $num_account_messages = \Drupal::service('social_private_message.service')->updateUnreadCount();

          // Default icon values.
          $label_classes = 'hidden';
          // Override icons when there are unread items.
          if ($num_account_messages > 0) {
            $label_classes = 'badge badge-accent badge--pill';
            $links['account_box']['classes'] = $links['account_box']['classes'] . ' has-alert';
          }
          $links['account_box']['below']['messages_mobile'] = [
            'classes' => 'mobile',
            'link_attributes' => '',
            'icon_classes' => '',
            'title' => $this->t('Inbox'),
            'label' => $this->t('Inbox'),
            'title_classes' => '',
            'count_classes' => $label_classes,
            'count_icon' => (string) $num_account_messages,
            'url' => Url::fromRoute('social_private_message.inbox'),
          ];
        }
      }

      if ($this->moduleHandler->moduleExists('activity_creator')) {
        $account_notifications = $this->activityNotifications;
        $num_notifications = count($account_notifications->getNotifications($account, [ACTIVITY_STATUS_RECEIVED]));

        if ($num_notifications === 0) {
          $label_classes = 'hidden';
        }
        else {
          $label_classes = 'badge badge-accent badge--pill';
          $links['account_box']['classes'] = $links['account_box']['classes'] . ' has-alert';

          if ($num_notifications > 99) {
            $num_notifications = '99+';
          }
        }

        $links['account_box']['below']['notification_mobile'] = [
          'classes' => 'mobile notification-bell',
          'link_attributes' => '',
          'icon_classes' => '',
          'title' => $this->t('Notification Centre'),
          'label' => $this->t('Notification Centre'),
          'title_classes' => '',
          'count_classes' => $label_classes,
          'count_icon' => (string) $num_notifications,
          'url' => Url::fromRoute('view.activity_stream_notifications.page_1'),
        ];
      }

      $storage = $this->entityTypeManager->getStorage('profile');
      $profile = $storage->loadByUser($account, 'profile');

      if ($profile) {
        $content = $this->entityTypeManager
          ->getViewBuilder('profile')
          ->view($profile, 'small');
        $links['account_box']['icon_image'] = $content;
      }

      $hook = 'social_user_account_header_links';

      $divider = [
        'divider' => 'true',
        'classes' => 'divider',
        'attributes' => 'role=separator',
      ];

      foreach ($this->moduleHandler->invokeAll($hook) as $key => $item) {
        if (!isset($links['account_box']['below'][$item['after']]) || isset($links['account_box']['below'][$key])) {
          continue;
        }

        $list = $links['account_box']['below'];

        $links['account_box']['below'] = [];

        foreach ($list as $exist_key => $exist_item) {
          $links['account_box']['below'][$exist_key] = $exist_item;

          if ($item['after'] == $exist_key) {
            if (isset($item['divider']) && $item['divider'] == 'before') {
              $links['account_box']['below'][$key . '_divider'] = $divider;
            }

            $links['account_box']['below'][$key] = [
              'classes' => '',
              'link_attributes' => '',
              'link_classes' => '',
              'icon_classes' => '',
              'icon_label' => '',
              'title' => $item['title'],
              'label' => $item['title'],
              'title_classes' => '',
              'url' => $item['url'],
            ];

            if (isset($item['divider']) && $item['divider'] == 'after') {
              $links['account_box']['below'][$key . '_divider'] = $divider;
            }
          }
        }
      }
    }
    else {
      $links = [
        'home' => [
          'classes' => 'hidden-xs',
          'link_attributes' => '',
          'icon_classes' => '',
          'icon_label' => 'Home',
          'title' => $this->t('Home'),
          'label' => $this->t('Home'),
          'title_classes' => '',
          'url' => Url::fromRoute('<front>'),
        ],
      ];
    }

    foreach (['add', 'account_box'] as $key) {
      if (!isset($links[$key]['below'])) {
        continue;
      }

      foreach ($links[$key]['below'] as &$item) {
        if (!isset($item['access']) && isset($item['url']) && $item['url'] instanceof Url) {
          $item['access'] = $item['url']->access($account);
        }
      }
    }

    if (isset($links['groups']['url']) && $links['groups']['url'] instanceof Url) {
      $links['groups']['access'] = $links['groups']['url']->access($account);
    }

    return [
      '#theme' => 'account_header_links',
      '#links' => $links,
      '#cache' => [
        'contexts' => [
          'user',
        ],
      ],
      '#attached' => [
        'library' => [
          'activity_creator/activity_creator.notifications',
        ],
      ],
    ];
  }

}
