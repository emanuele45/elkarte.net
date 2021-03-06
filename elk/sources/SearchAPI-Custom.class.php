<?php

/**
 * @name      ElkArte Forum
 * @copyright ElkArte Forum contributors
 * @license   BSD http://opensource.org/licenses/BSD-3-Clause
 *
 * This software is a derived product, based on:
 *
 * Simple Machines Forum (SMF)
 * copyright:	2011 Simple Machines (http://www.simplemachines.org)
 * license:  	BSD, See included LICENSE.TXT for terms and conditions.
 *
 * @version 1.0 Alpha
 *
 */

if (!defined('ELKARTE'))
	die('No access...');

/**
 * SearchAPI-Custom.php, Custom Search API class .. used when custom ELKARTE index is used
 */
class Custom_Search
{
	/**
	 *This is the last version of ELKARTE that this was tested on, to protect against API changes.
	 * @var string
	 */
	public $version_compatible = 'ELKARTE 1.0 Alpha';

	/**
	 *This won't work with versions of ELKARTE less than this.
	 * @var string
	 */
	public $min_elk_version = 'ELKARTE 1.0 Alpha';

	/**
	 * Is it supported?
	 * @var boolean
	 */
	public $is_supported = true;

	/**
	 * Index Settings
	 * @var array
	 */
	protected $indexSettings = array();

	/**
	 * What words are banned?
	 * @var array
	 */
	protected $bannedWords = array();

	/**
	 * What is the minimum word length?
	 * @var int
	 */
	protected $min_word_length = null;

	/**
	 * What databases support the custom index?
	 * @var array
	 */
	protected $supported_databases = array('mysql', 'postgresql', 'sqlite');

	/**
	 * constructor function
	 *
	 * @return type
	 */
	public function __construct()
	{
		global $modSettings, $db_type;

		// Is this database supported?
		if (!in_array($db_type, $this->supported_databases))
		{
			$this->is_supported = false;
			return;
		}

		if (empty($modSettings['search_custom_index_config']))
			return;

		$this->indexSettings = unserialize($modSettings['search_custom_index_config']);

		$this->bannedWords = empty($modSettings['search_stopwords']) ? array() : explode(',', $modSettings['search_stopwords']);
		$this->min_word_length = $this->indexSettings['bytes_per_word'];
	}

	/**
	 * Check whether the search can be performed by this API.
	 *
	 * @param type $methodName
	 * @param type $query_params
	 * @return boolean
	 */
	public function supportsMethod($methodName, $query_params = null)
	{
		switch ($methodName)
		{
			case 'isValid':
			case 'searchSort':
			case 'prepareIndexes':
			case 'indexedWordQuery':
			case 'postCreated':
			case 'postModified':
				return true;
			break;

			// All other methods, too bad dunno you.
			default:
				return false;
			return;
		}
	}

	/**
	 * If the settings don't exist we can't continue.
	 *
	 * @return type
	 */
	public function isValid()
	{
		global $modSettings;

		return !empty($modSettings['search_custom_index_config']);
	}

	/**
	 * callback function for usort used to sort the fulltext results.
	 * the order of sorting is: large words, small words, large words that
	 * are excluded from the search, small words that are excluded.
	 * @param string $a Word A
	 * @param string $b Word B
	 * @return int
	 */
	public function searchSort($a, $b)
	{
		global $excludedWords;

		$x = strlen($a) - (in_array($a, $excludedWords) ? 1000 : 0);
		$y = strlen($b) - (in_array($b, $excludedWords) ? 1000 : 0);

		return $y < $x ? 1 : ($y > $x ? -1 : 0);
	}

	/**
	 * Do we have to do some work with the words we are searching for to prepare them?
	 *
	 * @param string $word
	 * @param array $wordsSearch
	 * @param array $wordsExclude
	 * @param boolean $isExcluded
	 */
	public function prepareIndexes($word, &$wordsSearch, &$wordsExclude, $isExcluded)
	{
		global $modSettings, $smcFunc;

		$subwords = text2words($word, $this->min_word_length, true);

		if (empty($modSettings['search_force_index']))
			$wordsSearch['words'][] = $word;

		// Excluded phrases don't benefit from being split into subwords.
		if (count($subwords) > 1 && $isExcluded)
			continue;
		else
		{
			foreach ($subwords as $subword)
			{
				if ($smcFunc['strlen']($subword) >= $this->min_word_length && !in_array($subword, $this->bannedWords))
				{
					$wordsSearch['indexed_words'][] = $subword;
					if ($isExcluded)
						$wordsExclude[] = $subword;
				}
			}
		}
	}

	/**
	 * Search for indexed words.
	 *
	 * @param array $words
	 * @param array $search_data
	 * @return type
	 */
	public function indexedWordQuery($words, $search_data)
	{
		global $modSettings, $smcFunc;

		$query_select = array(
			'id_msg' => 'm.id_msg',
		);
		$query_inner_join = array();
		$query_left_join = array();
		$query_where = array();
		$query_params = $search_data['params'];

		if ($query_params['id_search'])
			$query_select['id_search'] = '{int:id_search}';

		$count = 0;
		foreach ($words['words'] as $regularWord)
		{
			$query_where[] = 'm.body' . (in_array($regularWord, $query_params['excluded_words']) ? ' NOT' : '') . (empty($modSettings['search_match_words']) || $search_data['no_regexp'] ? ' LIKE ' : ' RLIKE ') . '{string:complex_body_' . $count . '}';
			$query_params['complex_body_' . $count++] = empty($modSettings['search_match_words']) || $search_data['no_regexp'] ? '%' . strtr($regularWord, array('_' => '\\_', '%' => '\\%')) . '%' : '[[:<:]]' . addcslashes(preg_replace(array('/([\[\]$.+*?|{}()])/'), array('[$1]'), $regularWord), '\\\'') . '[[:>:]]';
		}

		if ($query_params['user_query'])
			$query_where[] = '{raw:user_query}';
		if ($query_params['board_query'])
			$query_where[] = 'm.id_board {raw:board_query}';

		if ($query_params['topic'])
			$query_where[] = 'm.id_topic = {int:topic}';
		if ($query_params['min_msg_id'])
			$query_where[] = 'm.id_msg >= {int:min_msg_id}';
		if ($query_params['max_msg_id'])
			$query_where[] = 'm.id_msg <= {int:max_msg_id}';

		$count = 0;
		if (!empty($query_params['excluded_phrases']) && empty($modSettings['search_force_index']))
			foreach ($query_params['excluded_phrases'] as $phrase)
			{
				$query_where[] = 'subject NOT ' . (empty($modSettings['search_match_words']) || $search_data['no_regexp'] ? ' LIKE ' : ' RLIKE ') . '{string:exclude_subject_phrase_' . $count . '}';
				$query_params['exclude_subject_phrase_' . $count++] = empty($modSettings['search_match_words']) || $search_data['no_regexp'] ? '%' . strtr($phrase, array('_' => '\\_', '%' => '\\%')) . '%' : '[[:<:]]' . addcslashes(preg_replace(array('/([\[\]$.+*?|{}()])/'), array('[$1]'), $phrase), '\\\'') . '[[:>:]]';
			}
		$count = 0;
		if (!empty($query_params['excluded_subject_words']) && empty($modSettings['search_force_index']))
			foreach ($query_params['excluded_subject_words'] as $excludedWord)
			{
				$query_where[] = 'subject NOT ' . (empty($modSettings['search_match_words']) || $search_data['no_regexp'] ? ' LIKE ' : ' RLIKE ') . '{string:exclude_subject_words_' . $count . '}';
				$query_params['exclude_subject_words_' . $count++] = empty($modSettings['search_match_words']) || $search_data['no_regexp'] ? '%' . strtr($excludedWord, array('_' => '\\_', '%' => '\\%')) . '%' : '[[:<:]]' . addcslashes(preg_replace(array('/([\[\]$.+*?|{}()])/'), array('[$1]'), $excludedWord), '\\\'') . '[[:>:]]';
			}

		$numTables = 0;
		$prev_join = 0;
		foreach ($words['indexed_words'] as $indexedWord)
		{
			$numTables++;
			if (in_array($indexedWord, $query_params['excluded_index_words']))
			{
				$query_left_join[] = '{db_prefix}log_search_words AS lsw' . $numTables . ' ON (lsw' . $numTables . '.id_word = ' . $indexedWord . ' AND lsw' . $numTables . '.id_msg = m.id_msg)';
				$query_where[] = '(lsw' . $numTables . '.id_word IS NULL)';
			}
			else
			{
				$query_inner_join[] = '{db_prefix}log_search_words AS lsw' . $numTables . ' ON (lsw' . $numTables . '.id_msg = ' . ($prev_join === 0 ? 'm' : 'lsw' . $prev_join) . '.id_msg)';
				$query_where[] = 'lsw' . $numTables . '.id_word = ' . $indexedWord;
				$prev_join = $numTables;
			}
		}

		$ignoreRequest = $smcFunc['db_search_query']('insert_into_log_messages_fulltext', ($smcFunc['db_support_ignore'] ? ( '
			INSERT IGNORE INTO {db_prefix}' . $search_data['insert_into'] . '
				(' . implode(', ', array_keys($query_select)) . ')') : '') . '
			SELECT ' . implode(', ', $query_select) . '
			FROM {db_prefix}messages AS m' . (empty($query_inner_join) ? '' : '
				INNER JOIN ' . implode('
				INNER JOIN ', $query_inner_join)) . (empty($query_left_join) ? '' : '
				LEFT JOIN ' . implode('
				LEFT JOIN ', $query_left_join)) . '
			WHERE ' . implode('
				AND ', $query_where) . (empty($search_data['max_results']) ? '' : '
			LIMIT ' . ($search_data['max_results'] - $search_data['indexed_results'])),
			$query_params
		);

		return $ignoreRequest;
	}

	/**
	 *  After a post is made, we update the search index database
	 *
	 * @param array $msgOptions
	 * @param array $topicOptions
	 * @param array $posterOptions
	 */
	public function postCreated($msgOptions, $topicOptions, $posterOptions)
	{
		global $modSettings, $smcFunc;

		$customIndexSettings = unserialize($modSettings['search_custom_index_config']);

		$inserts = array();
		foreach (text2words($msgOptions['body'], $customIndexSettings['bytes_per_word'], true) as $word)
			$inserts[] = array($word, $msgOptions['id']);

		if (!empty($inserts))
			$smcFunc['db_insert']('ignore',
				'{db_prefix}log_search_words',
				array('id_word' => 'int', 'id_msg' => 'int'),
				$inserts,
				array('id_word', 'id_msg')
			);
	}

	/**
	 * After a post is modified, we update the search index database.
	 *
	 * @param array $msgOptions
	 * @param array $topicOptions
	 * @param array $posterOptions
	 */
	public function postModified($msgOptions, $topicOptions, $posterOptions)
	{
		global $modSettings, $smcFunc;

		if (isset($msgOptions['body']))
		{
			$customIndexSettings = unserialize($modSettings['search_custom_index_config']);
			$stopwords = empty($modSettings['search_stopwords']) ? array() : explode(',', $modSettings['search_stopwords']);
			$old_body = isset($msgOptions['old_body']) ? $msgOptions['old_body'] : '';

			// create thew new and old index
			$old_index = text2words($old_body, $customIndexSettings['bytes_per_word'], true);
			$new_index = text2words($msgOptions['body'], $customIndexSettings['bytes_per_word'], true);

			// Calculate the words to be added and removed from the index.
			$removed_words = array_diff(array_diff($old_index, $new_index), $stopwords);
			$inserted_words = array_diff(array_diff($new_index, $old_index), $stopwords);

			// Delete the removed words AND the added ones to avoid key constraints.
			if (!empty($removed_words))
			{
				$removed_words = array_merge($removed_words, $inserted_words);
				$smcFunc['db_query']('', '
					DELETE FROM {db_prefix}log_search_words
					WHERE id_msg = {int:id_msg}
						AND id_word IN ({array_int:removed_words})',
					array(
						'removed_words' => $removed_words,
						'id_msg' => $msgOptions['id'],
					)
				);
			}

			// Add the new words to be indexed.
			if (!empty($inserted_words))
			{
				$inserts = array();
				foreach ($inserted_words as $word)
					$inserts[] = array($word, $msgOptions['id']);
				$smcFunc['db_insert']('insert',
					'{db_prefix}log_search_words',
					array('id_word' => 'string', 'id_msg' => 'int'),
					$inserts,
					array('id_word', 'id_msg')
				);
			}
		}
	}
}