<?php

namespace Drupal\group_bonus\Plugin\Linkit\Matcher;

use Drupal\Core\Entity\EntityInterface;
use Drupal\group\Entity\GroupContent;
use Drupal\linkit\Plugin\Linkit\Matcher\NodeMatcher;

/**
 * A linkit matcher that will also show the label of the group, if the entity is
 * part of a group.
 *
 * @Matcher(
 *   id = "group_entity:node",
 *   label = @Translation("Content with optional group support"),
 *   target_entity = "node",
 *   provider = "node"
 * )
 */
class GroupNodeMatcher extends NodeMatcher {

  /**
   * {@inheritdoc}
   */
  public function buildLabel(EntityInterface $entity) {
    $label = parent::buildLabel($entity);
    $group_content = GroupContent::loadByEntity($entity);
    if (!empty($group_content)) {
      $group_content = reset($group_content);
      $label .= ' (' . $group_content->getGroup()->label() . ')';
    }
    return $label;
  }
}
