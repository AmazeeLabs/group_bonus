<?php

/**
 * @file
 *  Contains \Drupal\group_bonus\Controller\GroupBonusRedirectToGroup
 */

namespace Drupal\group_bonus\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupContent;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class GroupBonusRedirectToGroup extends ControllerBase {

  /**
   * Route callback to redirect to the first group of the node.
   */
  public function redirectToGroup(NodeInterface $node) {
    $group_content = GroupContent::loadByEntity($node);
    $group_content = reset($group_content);
    /* @var Group $group */
    $group = $group_content->getGroup();
    $redirect_url = Url::fromRoute('entity.group.canonical', array('group' => $group->id()));
    return new RedirectResponse($redirect_url->toString());
  }

  /**
   * Checks if the user has access to this route.
   */
  public function redirectAccess(AccountInterface $account, NodeInterface $node) {
    $group_content = GroupContent::loadByEntity($node);
    if (!empty($group_content)) {
      $group_content = reset($group_content);
      /* @var Group $group */
      $group = $group_content->getGroup();
      // We have a group, so check if we can view the page.
      return $group->access('view', $account, TRUE);
    }
    return AccessResult::forbidden();
  }
}
