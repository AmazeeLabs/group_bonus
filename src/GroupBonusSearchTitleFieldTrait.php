<?php

namespace Drupal\group_bonus;


use Drupal\Core\Database\Database;
use Drupal\Core\Database\Query\Condition;
use Drupal\Core\Database\Query\SelectInterface;

/**
 * A trait that adds the functionality to alter a query which searches in the
 * title of an entity to search in the field_search_title field.
 */
trait GroupBonusSearchTitleFieldTrait {

  /**
   * Alters a select query so that it searches in the field_search_title, if
   * there field is not empty.
   *
   * @param \Drupal\Core\Database\Query\SelectInterface $query
   *   The select query to alter.
   * @param string $match_text
   *   The text that is being searched for.
   */
  public function alterSelectQuery(SelectInterface $query, $match_text) {
    // The search logic is this: if the node has a non-empty value in the search
    // title field, then we will use that field for searching. If the node has
    // no value in the search title field, then we will use the original title
    // field.
    // Maybe for the future, if this functionality will be needed in some other
    // places: we could implement a more general solution, with the user being
    // able to say in which fields to search besides the title field.

    // For now, we have some hard coded values for the query table and field
    // names. First thing to do is to remove the old conditions on the title
    // field.

    $conditions = &$query->conditions();
    foreach ($conditions as $index => $cond) {
      // Important: this does not work right now with nested queries! (but we
      // should not have nested queries from the parent class at the moment).
      if (is_array($cond) && !empty($cond['field']) && $cond['field'] == 'node_field_data.title') {
        unset($conditions[$index]);
      }
    }

    // Instead of searching the entire match, we will split it into words (we
    // consider that the words are either delimited by space, or the starting
    // and the ending of a string). This means we basically split the original
    // value by 'space'.
    $words = explode(' ', $match_text);
    $words = array_map('trim', $words);
    $words = array_filter($words, 'strlen');

    // We can now join with the search title table and implement our special
    // conditions.
    $query->leftJoin('node__field_search_title', 'nfst', 'node_field_data.nid = nfst.entity_id AND node_field_data.langcode=nfst.langcode');
    $search_title_condition = new Condition('AND');
    $title_condition = new Condition('AND');
    $search_title_condition->isNotNull('nfst.field_search_title_value')->condition('nfst.field_search_title_value', '', '<>');
    $title_condition->condition($title_condition->orConditionGroup()->isNull('nfst.field_search_title_value')->condition('nfst.field_search_title_value', '', '='));
    foreach ($words as $word) {
      $search_title_condition->condition('nfst.field_search_title_value', '%' . Database::getConnection()->escapeLike($word) . '%', 'LIKE');
      $title_condition->condition('node_field_data.title', '%' . Database::getConnection()->escapeLike($word) . '%', 'LIKE');
    }
    $query->condition($query->orConditionGroup()->condition($search_title_condition)->condition($title_condition));
  }
}
