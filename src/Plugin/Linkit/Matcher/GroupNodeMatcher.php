<?php

namespace Drupal\group_bonus\Plugin\Linkit\Matcher;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\group\Entity\GroupContent;
use Drupal\group_bonus\GroupBonusSearchTitleFieldTrait;
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

  use GroupBonusSearchTitleFieldTrait;

  protected $searchString;

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

  /**
   * {@inheritdoc}
   */
  public function buildEntityQuery($search_string) {
    $query = parent::buildEntityQuery($search_string);

    // Add the Selection handler for system_query_entity_reference_alter().
    // By doing this, the system_query_entity_reference_alter() will invoke
    // the entityQueryAlter() method on our class.
    $query->addTag('entity_reference');
    $query->addMetaData('entity_reference_selection_handler', $this);

    $this->searchString = $search_string;
    return $query;
  }

  /**
   * Alters the entity query.
   *
   * We can implement this method because we add the 'entity_reference' tag to
   * the query in ::buildEntityQuery().
   */
  public function entityQueryAlter(SelectInterface $query) {
    $this->alterSelectQuery($query, $this->searchString);
  }
}
