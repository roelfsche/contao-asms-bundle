<?php

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao;

use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Exception\InternalServerErrorException;
use Contao\CoreBundle\Exception\ResponseException;
// use Contao\CoreBundle\Picker\PickerInterface;
// use Patchwork\Utf8;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Provide methods to modify the database.
 *
 * @property integer $id
 * @property string  $parentTable
 * @property array   $childTable
 * @property boolean $createNewVersion
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 * @author Andreas Schempp <https://github.com/aschempp>
 */
class DC_JobTable extends DC_Table //\DataContainer implements \listable, \editable
{
	/**
	 * List all records of the current table and return them as HTML string
	 *
	 * @return string
	 */
	protected function listView()
	{
		$this->import('BackendUser', 'User');
		$objUser = $this->User;
		$table = ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 6) ? $this->ptable : $this->strTable;
		$orderBy = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields'];
		$firstOrderBy = preg_replace('/\s+.*$/', '', $orderBy[0]);

		if (\is_array($this->orderBy) && $this->orderBy[0] != '') {
			$orderBy = $this->orderBy;
			$firstOrderBy = $this->firstOrderBy;
		}

		$query = "SELECT * FROM " . $this->strTable;

		if (!empty($this->procedure)) {
			$query .= " WHERE " . implode(' AND ', $this->procedure);
		}

		if (!empty($this->root) && \is_array($this->root)) {
			$query .= (!empty($this->procedure) ? " AND " : " WHERE ") . "id IN(" . implode(',', array_map('\intval', $this->root)) . ")";
		}

		// nur die Jobs, deren Kliniken dem User zugeordnet sind
		if (!$objUser->isAdmin) {
			$arrAllowedClinics = $objUser->job_offer_access;
			if (!$arrAllowedClinics) {
				$arrAllowedClinics = [-1];
			}
			if (strpos($query, 'WHERE') !== FALSE) {
				$query .= ' AND (' . strtr('clinic in (?)', array('?' => implode(',', $arrAllowedClinics))) . ' OR clinic IS NULL)';
			} else {
				$query .= ' WHERE (' . strtr('clinic in (?)', array('?' => implode(',', $arrAllowedClinics))) . ' OR clinic IS NULL)';
			}
		}
		if (\is_array($orderBy) && $orderBy[0] != '') {
			foreach ($orderBy as $k => $v) {
				list($key, $direction) = explode(' ', $v, 2);

				// If there is no direction, check the global flag in sorting mode 1 or the field flag in all other sorting modes
				if (!$direction) {
					if ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 1 && isset($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['flag']) && ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['flag'] % 2) == 0) {
						$direction = 'DESC';
					} elseif (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['flag']) && ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['flag'] % 2) == 0) {
						$direction = 'DESC';
					}
				}

				if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['eval']['findInSet']) {
					$direction = null;

					if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['options_callback'])) {
						$strClass = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['options_callback'][0];
						$strMethod = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['options_callback'][1];

						$this->import($strClass);
						$keys = $this->$strClass->$strMethod($this);
					} elseif (\is_callable($GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['options_callback'])) {
						$keys = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['options_callback']($this);
					} else {
						$keys = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['options'];
					}

					if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['eval']['isAssociative'] || array_is_assoc($keys)) {
						$keys = array_keys($keys);
					}

					$orderBy[$k] = $this->Database->findInSet($v, $keys);
				} elseif (\in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['flag'], array(5, 6, 7, 8, 9, 10))) {
					$orderBy[$k] = "CAST($key AS SIGNED)"; // see #5503
				}

				if ($direction) {
					$orderBy[$k] = $key . ' ' . $direction;
				}
			}

			if ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 3) {
				$firstOrderBy = 'pid';
				$showFields = $GLOBALS['TL_DCA'][$table]['list']['label']['fields'];

				$query .= " ORDER BY (SELECT " . \Database::quoteIdentifier($showFields[0]) . " FROM " . $this->ptable . " WHERE " . $this->ptable . ".id=" . $this->strTable . ".pid), " . implode(', ', $orderBy);

				// Set the foreignKey so that the label is translated
				if ($GLOBALS['TL_DCA'][$table]['fields']['pid']['foreignKey'] == '') {
					$GLOBALS['TL_DCA'][$table]['fields']['pid']['foreignKey'] = $this->ptable . '.' . $showFields[0];
				}

				// Remove the parent field from label fields
				array_shift($showFields);
				$GLOBALS['TL_DCA'][$table]['list']['label']['fields'] = $showFields;
			} else {
				$query .= " ORDER BY " . implode(', ', $orderBy);
			}
		}

		$objRowStmt = $this->Database->prepare($query);

		if ($this->limit != '') {
			$arrLimit = explode(',', $this->limit);
			$objRowStmt->limit($arrLimit[1], $arrLimit[0]);
		}

		$objRow = $objRowStmt->execute($this->values);
		$this->total = $objRow->numRows;

		// Display buttos
		$return = \Message::generate() . '
<div id="tl_buttons">' . ((\Input::get('act') == 'select' || $this->ptable) ? '
<a href="' . $this->getReferer(true, $this->ptable) . '" class="header_back" title="' . \StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']) . '" accesskey="b" onclick="Backend.getScrollOffset()">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a> ' : (isset($GLOBALS['TL_DCA'][$this->strTable]['config']['backlink']) ? '
<a href="contao/main.php?' . $GLOBALS['TL_DCA'][$this->strTable]['config']['backlink'] . '" class="header_back" title="' . \StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']) . '" accesskey="b" onclick="Backend.getScrollOffset()">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a> ' : '')) . ((\Input::get('act') != 'select' && !$GLOBALS['TL_DCA'][$this->strTable]['config']['closed'] && !$GLOBALS['TL_DCA'][$this->strTable]['config']['notCreatable']) ? '
<a href="' . (($this->ptable != '') ? $this->addToUrl('act=create' . (($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] < 4) ? '&amp;mode=2' : '') . '&amp;pid=' . $this->intId) : $this->addToUrl('act=create')) . '" class="header_new" title="' . \StringUtil::specialchars($GLOBALS['TL_LANG'][$this->strTable]['new'][1]) . '" accesskey="n" onclick="Backend.getScrollOffset()">' . $GLOBALS['TL_LANG'][$this->strTable]['new'][0] . '</a> ' : '') . $this->generateGlobalButtons() . '
</div>';

		// Return "no records found" message
		if ($objRow->numRows < 1) {
			$return .= '
<p class="tl_empty">' . $GLOBALS['TL_LANG']['MSC']['noResult'] . '</p>';
		}

		// List records
		else {
			$result = $objRow->fetchAllAssoc();

			$return .= ((\Input::get('act') == 'select') ? '
<form action="' . ampersand(\Environment::get('request'), true) . '" id="tl_select" class="tl_form' . ((\Input::get('act') == 'select') ? ' unselectable' : '') . '" method="post" novalidate>
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="tl_select">
<input type="hidden" name="REQUEST_TOKEN" value="' . REQUEST_TOKEN . '">' : '') . '
<div class="tl_listing_container list_view" id="tl_listing">' . ((\Input::get('act') == 'select' || $this->strPickerFieldType == 'checkbox') ? '
<div class="tl_select_trigger">
<label for="tl_select_trigger" class="tl_select_label">' . $GLOBALS['TL_LANG']['MSC']['selectAll'] . '</label> <input type="checkbox" id="tl_select_trigger" onclick="Backend.toggleCheckboxes(this)" class="tl_tree_checkbox">
</div>' : '') . '
<table class="tl_listing' . ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns'] ? ' showColumns' : '') . ($this->strPickerFieldType ? ' picker unselectable' : '') . '">';

			// Automatically add the "order by" field as last column if we do not have group headers
			if ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns']) {
				$blnFound = false;

				// Extract the real key and compare it to $firstOrderBy
				foreach ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'] as $f) {
					if (strpos($f, ':') !== false) {
						list($f) = explode(':', $f, 2);
					}

					if ($firstOrderBy == $f) {
						$blnFound = true;
						break;
					}
				}

				if (!$blnFound) {
					$GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'][] = $firstOrderBy;
				}
			}

			// Generate the table header if the "show columns" option is active
			if ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns']) {
				$return .= '
  <tr>';

				foreach ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'] as $f) {
					if (strpos($f, ':') !== false) {
						list($f) = explode(':', $f, 2);
					}

					$return .= '
    <th class="tl_folder_tlist col_' . $f . (($f == $firstOrderBy) ? ' ordered_by' : '') . '">' . (\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$f]['label']) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$f]['label'][0] : $GLOBALS['TL_DCA'][$this->strTable]['fields'][$f]['label']) . '</th>';
				}

				$return .= '
    <th class="tl_folder_tlist tl_right_nowrap"></th>
  </tr>';
			}

			// Process result and add label and buttons
			$remoteCur = false;
			$groupclass = 'tl_folder_tlist';
			$eoCount = -1;

			foreach ($result as $row) {
				$args = array();
				$this->current[] = $row['id'];
				$showFields = $GLOBALS['TL_DCA'][$table]['list']['label']['fields'];

				// Label
				foreach ($showFields as $k => $v) {
					// Decrypt the value
					if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['encrypt']) {
						$row[$v] = \Encryption::decrypt(\StringUtil::deserialize($row[$v]));
					}

					if (strpos($v, ':') !== false) {
						list($strKey, $strTable) = explode(':', $v);
						list($strTable, $strField) = explode('.', $strTable);

						$objRef = $this->Database->prepare("SELECT " . \Database::quoteIdentifier($strField) . " FROM " . $strTable . " WHERE id=?")
							->limit(1)
							->execute($row[$strKey]);

						$args[$k] = $objRef->numRows ? $objRef->$strField : '';
					} elseif (\in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['flag'], array(5, 6, 7, 8, 9, 10))) {
						if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['rgxp'] == 'date') {
							$args[$k] = $row[$v] ? \Date::parse(\Config::get('dateFormat'), $row[$v]) : '-';
						} elseif ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['rgxp'] == 'time') {
							$args[$k] = $row[$v] ? \Date::parse(\Config::get('timeFormat'), $row[$v]) : '-';
						} else {
							$args[$k] = $row[$v] ? \Date::parse(\Config::get('datimFormat'), $row[$v]) : '-';
						}
					} elseif ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType'] == 'checkbox' && !$GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['multiple']) {
						$args[$k] = $row[$v] ? $GLOBALS['TL_LANG']['MSC']['yes'] : $GLOBALS['TL_LANG']['MSC']['no'];
					} else {
						$row_v = \StringUtil::deserialize($row[$v]);

						if (\is_array($row_v)) {
							$args_k = array();

							foreach ($row_v as $option) {
								$args_k[] = $GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$option] ?: $option;
							}

							$args[$k] = implode(', ', $args_k);
						} elseif (isset($GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$row[$v]])) {
							$args[$k] = \is_array($GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$row[$v]]) ? $GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$row[$v]][0] : $GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$row[$v]];
						} elseif (($GLOBALS['TL_DCA'][$table]['fields'][$v]['eval']['isAssociative'] || array_is_assoc($GLOBALS['TL_DCA'][$table]['fields'][$v]['options'])) && isset($GLOBALS['TL_DCA'][$table]['fields'][$v]['options'][$row[$v]])) {
							$args[$k] = $GLOBALS['TL_DCA'][$table]['fields'][$v]['options'][$row[$v]];
						} else {
							$args[$k] = $row[$v];
						}
					}
				}

				// Shorten the label it if it is too long
				$label = vsprintf($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['format'] ?: '%s', $args);

				if ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['maxCharacters'] > 0 && $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['maxCharacters'] < \strlen(strip_tags($label))) {
					$label = trim(\StringUtil::substrHtml($label, $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['maxCharacters'])) . ' …';
				}

				// Remove empty brackets (), [], {}, <> and empty tags from the label
				$label = preg_replace('/\( *\) ?|\[ *] ?|{ *} ?|< *> ?/', '', $label);
				$label = preg_replace('/<[^>]+>\s*<\/[^>]+>/', '', $label);

				// Build the sorting groups
				if ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] > 0) {
					$current = $row[$firstOrderBy];
					$orderBy = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields'];
					$sortingMode = (\count($orderBy) == 1 && $firstOrderBy == $orderBy[0] && $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['flag'] != '' && $GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['flag'] == '') ? $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['flag'] : $GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['flag'];
					$remoteNew = $this->formatCurrentValue($firstOrderBy, $current, $sortingMode);

					// Add the group header
					if (!$GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns'] && !$GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['disableGrouping'] && ($remoteNew != $remoteCur || $remoteCur === false)) {
						$eoCount = -1;
						$group = $this->formatGroupHeader($firstOrderBy, $remoteNew, $sortingMode, $row);
						$remoteCur = $remoteNew;

						$return .= '
  <tr>
    <td colspan="2" class="' . $groupclass . '">' . $group . '</td>
  </tr>';
						$groupclass = 'tl_folder_list';
					}
				}

				$return .= '
  <tr class="' . ((++$eoCount % 2 == 0) ? 'even' : 'odd') . ' click2edit toggle_select hover-row">
    ';

				$colspan = 1;

				// Call the label_callback ($row, $label, $this)
				if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback']) || \is_callable($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback'])) {
					if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback'])) {
						$strClass = $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback'][0];
						$strMethod = $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback'][1];

						$this->import($strClass);
						$args = $this->$strClass->$strMethod($row, $label, $this, $args);
					} elseif (\is_callable($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback'])) {
						$args = $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback']($row, $label, $this, $args);
					}

					// Handle strings and arrays
					if (!$GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns']) {
						$label = \is_array($args) ? implode(' ', $args) : $args;
					} elseif (!\is_array($args)) {
						$args = array($args);
						$colspan = \count($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields']);
					}
				}

				// Show columns
				if ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns']) {
					foreach ($args as $j => $arg) {
						$return .= '<td colspan="' . $colspan . '" class="tl_file_list col_' . $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'][$j] . (($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'][$j] == $firstOrderBy) ? ' ordered_by' : '') . '">' . ($arg ?: '-') . '</td>';
					}
				} else {
					$return .= '<td class="tl_file_list">' . $label . '</td>';
				}

				// Buttons ($row, $table, $root, $blnCircularReference, $childs, $previous, $next)
				$return .= ((\Input::get('act') == 'select') ? '
    <td class="tl_file_list tl_right_nowrap"><input type="checkbox" name="IDS[]" id="ids_' . $row['id'] . '" class="tl_tree_checkbox" value="' . $row['id'] . '"></td>' : '
    <td class="tl_file_list tl_right_nowrap">' . $this->generateButtons($row, $this->strTable, $this->root) . ($this->strPickerFieldType ? $this->getPickerInputField($row['id']) : '') . '</td>') . '
  </tr>';
			}

			// Close the table
			$return .= '
</table>' . ($this->strPickerFieldType == 'radio' ? '
<div class="tl_radio_reset">
<label for="tl_radio_reset" class="tl_radio_label">' . $GLOBALS['TL_LANG']['MSC']['resetSelected'] . '</label> <input type="radio" name="picker" id="tl_radio_reset" value="" class="tl_tree_radio">
</div>' : '') . '
</div>';

			// Close the form
			if (\Input::get('act') == 'select') {
				// Submit buttons
				$arrButtons = array();

				if (!$GLOBALS['TL_DCA'][$this->strTable]['config']['notEditable']) {
					$arrButtons['edit'] = '<button type="submit" name="edit" id="edit" class="tl_submit" accesskey="s">' . $GLOBALS['TL_LANG']['MSC']['editSelected'] . '</button>';
				}

				if (!$GLOBALS['TL_DCA'][$this->strTable]['config']['notDeletable']) {
					$arrButtons['delete'] = '<button type="submit" name="delete" id="delete" class="tl_submit" accesskey="d" onclick="return confirm(\'' . $GLOBALS['TL_LANG']['MSC']['delAllConfirm'] . '\')">' . $GLOBALS['TL_LANG']['MSC']['deleteSelected'] . '</button>';
				}

				if (!$GLOBALS['TL_DCA'][$this->strTable]['config']['notCopyable']) {
					$arrButtons['copy'] = '<button type="submit" name="copy" id="copy" class="tl_submit" accesskey="c">' . $GLOBALS['TL_LANG']['MSC']['copySelected'] . '</button>';
				}

				if (!$GLOBALS['TL_DCA'][$this->strTable]['config']['notEditable']) {
					$arrButtons['override'] = '<button type="submit" name="override" id="override" class="tl_submit" accesskey="v">' . $GLOBALS['TL_LANG']['MSC']['overrideSelected'] . '</button>';
				}

				// Call the buttons_callback (see #4691)
				if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['select']['buttons_callback'])) {
					foreach ($GLOBALS['TL_DCA'][$this->strTable]['select']['buttons_callback'] as $callback) {
						if (\is_array($callback)) {
							$this->import($callback[0]);
							$arrButtons = $this->{$callback[0]}->{$callback[1]}($arrButtons, $this);
						} elseif (\is_callable($callback)) {
							$arrButtons = $callback($arrButtons, $this);
						}
					}
				}

				if (\count($arrButtons) < 3) {
					$strButtons = implode(' ', $arrButtons);
				} else {
					$strButtons = array_shift($arrButtons) . ' ';
					$strButtons .= '<div class="split-button">';
					$strButtons .= array_shift($arrButtons) . '<button type="button" id="sbtog">' . \Image::getHtml('navcol.svg') . '</button> <ul class="invisible">';

					foreach ($arrButtons as $strButton) {
						$strButtons .= '<li>' . $strButton . '</li>';
					}

					$strButtons .= '</ul></div>';
				}

				$return .= '
</div>
<div class="tl_formbody_submit" style="text-align:right">
<div class="tl_submit_container">
  ' . $strButtons . '
</div>
</div>
</form>';
			}
		}

		return $return;
	}

	/**
	 * Return a select menu to limit results
	 *
	 * @param boolean $blnOptional
	 *
	 * @return string
	 */
	protected function limitMenu($blnOptional = false)
	{
		$this->import('BackendUser', 'User');
		$objUser = $this->User;

		/** @var AttributeBagInterface $objSessionBag */
		$objSessionBag = \System::getContainer()->get('session')->getBag('contao_backend');

		$session = $objSessionBag->all();
		$filter = ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 4) ? $this->strTable . '_' . CURRENT_ID : $this->strTable;
		$fields = '';

		// Set limit from user input
		if (\Input::post('FORM_SUBMIT') == 'tl_filters' || \Input::post('FORM_SUBMIT') == 'tl_filters_limit') {
			$strLimit = \Input::post('tl_limit');

			if ($strLimit == 'tl_limit') {
				unset($session['filter'][$filter]['limit']);
			} else {
				// Validate the user input (thanks to aulmn) (see #4971)
				if ($strLimit == 'all' || preg_match('/^[0-9]+,[0-9]+$/', $strLimit)) {
					$session['filter'][$filter]['limit'] = $strLimit;
				}
			}

			$objSessionBag->replace($session);

			if (\Input::post('FORM_SUBMIT') == 'tl_filters_limit') {
				$this->reload();
			}
		}

		// Set limit from table configuration
		else {
			$this->limit = ($session['filter'][$filter]['limit'] != '') ? (($session['filter'][$filter]['limit'] == 'all') ? null : $session['filter'][$filter]['limit']) : '0,' . \Config::get('resultsPerPage');

			$arrProcedure = $this->procedure;
			$arrValues = $this->values;
			$query = "SELECT COUNT(*) AS count FROM " . $this->strTable;

			if (!empty($this->root) && \is_array($this->root)) {
				$arrProcedure[] = 'id IN(' . implode(',', $this->root) . ')';
			}

			// Support empty ptable fields
			if ($GLOBALS['TL_DCA'][$this->strTable]['config']['dynamicPtable']) {
				$arrProcedure[] = ($this->ptable == 'tl_article') ? "(ptable=? OR ptable='')" : "ptable=?";
				$arrValues[] = $this->ptable;
			}

			if (!empty($arrProcedure)) {
				$query .= " WHERE " . implode(' AND ', $arrProcedure);
			}

			if (!$objUser->isAdmin) {
				$arrAllowedClinics = $objUser->job_offer_access;
				if (!$arrAllowedClinics) {
					$arrAllowedClinics = [-1];
				}
				if (strpos($query, 'WHERE') !== FALSE) {
					$query .= ' AND (' . strtr('clinic in (?)', array('?' => implode(',', $arrAllowedClinics))) . ' OR clinic IS NULL)';
				} else {
					$query .= ' WHERE (' . strtr('clinic in (?)', array('?' => implode(',', $arrAllowedClinics))) . ' OR clinic IS NULL)';
				}
			}


			$objTotal = $this->Database->prepare($query)->execute($arrValues);
			$this->total = $objTotal->count;
			$options_total = 0;
			$blnIsMaxResultsPerPage = false;

			// Overall limit
			if (\Config::get('maxResultsPerPage') > 0 && $this->total > \Config::get('maxResultsPerPage') && ($this->limit === null || preg_replace('/^.*,/', '', $this->limit) == \Config::get('maxResultsPerPage'))) {
				if ($this->limit === null) {
					$this->limit = '0,' . \Config::get('maxResultsPerPage');
				}

				$blnIsMaxResultsPerPage = true;
				\Config::set('resultsPerPage', \Config::get('maxResultsPerPage'));
				$session['filter'][$filter]['limit'] = \Config::get('maxResultsPerPage');
			}

			$options = '';

			// Build options
			if ($this->total > 0) {
				$options = '';
				$options_total = ceil($this->total / \Config::get('resultsPerPage'));

				// Reset limit if other parameters have decreased the number of results
				if ($this->limit !== null && ($this->limit == '' || preg_replace('/,.*$/', '', $this->limit) > $this->total)) {
					$this->limit = '0,' . \Config::get('resultsPerPage');
				}

				// Build options
				for ($i = 0; $i < $options_total; $i++) {
					$this_limit = ($i * \Config::get('resultsPerPage')) . ',' . \Config::get('resultsPerPage');
					$upper_limit = ($i * \Config::get('resultsPerPage') + \Config::get('resultsPerPage'));

					if ($upper_limit > $this->total) {
						$upper_limit = $this->total;
					}

					$options .= '
  <option value="' . $this_limit . '"' . \Widget::optionSelected($this->limit, $this_limit) . '>' . ($i * \Config::get('resultsPerPage') + 1) . ' - ' . $upper_limit . '</option>';
				}

				if (!$blnIsMaxResultsPerPage) {
					$options .= '
  <option value="all"' . \Widget::optionSelected($this->limit, null) . '>' . $GLOBALS['TL_LANG']['MSC']['filterAll'] . '</option>';
				}
			}

			// Return if there is only one page
			if ($blnOptional && ($this->total < 1 || $options_total < 2)) {
				return '';
			}

			$fields = '
<select name="tl_limit" class="tl_select' . (($session['filter'][$filter]['limit'] != 'all' && $this->total > \Config::get('resultsPerPage')) ? ' active' : '') . '" onchange="this.form.submit()">
  <option value="tl_limit">' . $GLOBALS['TL_LANG']['MSC']['filterRecords'] . '</option>' . $options . '
</select> ';
		}

		return '
<div class="tl_limit tl_subpanel">
<strong>' . $GLOBALS['TL_LANG']['MSC']['showOnly'] . ':</strong> ' . $fields . '
</div>';
	}

	/**
	 * Checke, ob der User den auch darf (check gegen erlaubte Kliniken)
	 *
	 * @param integer $intId
	 * @param integer $ajaxId
	 *
	 * @return string
	 *
	 * @throws AccessDeniedException
	 * @throws InternalServerErrorException
	 */
	public function edit($intId = null, $ajaxId = null)
	{
		if ($GLOBALS['TL_DCA'][$this->strTable]['config']['notEditable']) {
			throw new InternalServerErrorException('Table "' . $this->strTable . '" is not editable.');
		}

		if ($intId != '') {
			$this->intId = $intId;
		}

		// Get the current record
		$this->import('BackendUser', 'User');
		$objUser = $this->User;
		$query = "SELECT * FROM " . $this->strTable . " WHERE id=?";
		if (!$objUser->isAdmin) {
			$arrAllowedClinics = $objUser->job_offer_access;
			if (!$arrAllowedClinics) {
				$arrAllowedClinics = [-1];
			}
			$query .= ' AND (' . strtr('clinic in (?)', array('?' => implode(',', $arrAllowedClinics))) . ' OR clinic IS NULL)';
		}

		$objRow = $this->Database->prepare($query)
			->limit(1)
			->execute($this->intId);

		// Redirect if there is no record with the given ID
		if ($objRow->numRows < 1) {
			throw new AccessDeniedException('Cannot load record "' . $this->strTable . '.id=' . $this->intId . '".');
		}

		$this->objActiveRecord = $objRow;

		$return = '';
		$this->values[] = $this->intId;
		$this->procedure[] = 'id=?';

		$this->blnCreateNewVersion = false;
		$objVersions = new \Versions($this->strTable, $this->intId);

		if (!$GLOBALS['TL_DCA'][$this->strTable]['config']['hideVersionMenu']) {
			// Compare versions
			if (\Input::get('versions')) {
				$objVersions->compare();
			}

			// Restore a version
			if (\Input::post('FORM_SUBMIT') == 'tl_version' && \Input::post('version') != '') {
				$objVersions->restore(\Input::post('version'));
				$this->reload();
			}
		}

		$objVersions->initialize();

		// Build an array from boxes and rows
		$this->strPalette = $this->getPalette();
		$boxes = \StringUtil::trimsplit(';', $this->strPalette);
		$legends = array();

		if (!empty($boxes)) {
			foreach ($boxes as $k => $v) {
				$eCount = 1;
				$boxes[$k] = \StringUtil::trimsplit(',', $v);

				foreach ($boxes[$k] as $kk => $vv) {
					if (preg_match('/^\[.*]$/', $vv)) {
						++$eCount;
						continue;
					}

					if (preg_match('/^{.*}$/', $vv)) {
						$legends[$k] = substr($vv, 1, -1);
						unset($boxes[$k][$kk]);
					} elseif ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$vv]['exclude'] || !\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$vv])) {
						unset($boxes[$k][$kk]);
					}
				}

				// Unset a box if it does not contain any fields
				if (\count($boxes[$k]) < $eCount) {
					unset($boxes[$k]);
				}
			}

			/** @var Session $objSessionBag */
			$objSessionBag = \System::getContainer()->get('session')->getBag('contao_backend');

			$class = 'tl_tbox';
			$fs = $objSessionBag->get('fieldset_states');

			// Render boxes
			foreach ($boxes as $k => $v) {
				$arrAjax = array();
				$blnAjax = false;
				$key = '';
				$cls = '';
				$legend = '';

				if (isset($legends[$k])) {
					list($key, $cls) = explode(':', $legends[$k]);
					$legend = "\n" . '<legend onclick="AjaxRequest.toggleFieldset(this,\'' . $key . '\',\'' . $this->strTable . '\')">' . (isset($GLOBALS['TL_LANG'][$this->strTable][$key]) ? $GLOBALS['TL_LANG'][$this->strTable][$key] : $key) . '</legend>';
				}

				if (isset($fs[$this->strTable][$key])) {
					$class .= ($fs[$this->strTable][$key] ? '' : ' collapsed');
				} else {
					$class .= (($cls && $legend) ? ' ' . $cls : '');
				}

				$return .= "\n\n" . '<fieldset' . ($key ? ' id="pal_' . $key . '"' : '') . ' class="' . $class . ($legend ? '' : ' nolegend') . '">' . $legend;
				$thisId = '';

				// Build rows of the current box
				foreach ($v as $vv) {
					if ($vv == '[EOF]') {
						if ($blnAjax && \Environment::get('isAjaxRequest')) {
							if ($ajaxId == $thisId) {
								return $arrAjax[$thisId] . '<input type="hidden" name="FORM_FIELDS[]" value="' . \StringUtil::specialchars($this->strPalette) . '">';
							}

							if (\count($arrAjax) > 1) {
								$current = "\n" . '<div id="' . $thisId . '" class="subpal cf">' . $arrAjax[$thisId] . '</div>';
								unset($arrAjax[$thisId]);
								end($arrAjax);
								$thisId = key($arrAjax);
								$arrAjax[$thisId] .= $current;
							}
						}

						$return .= "\n" . '</div>';

						continue;
					}

					if (preg_match('/^\[.*]$/', $vv)) {
						$thisId = 'sub_' . substr($vv, 1, -1);
						$arrAjax[$thisId] = '';
						$blnAjax = ($ajaxId == $thisId && \Environment::get('isAjaxRequest')) ? true : $blnAjax;
						$return .= "\n" . '<div id="' . $thisId . '" class="subpal cf">';

						continue;
					}

					$this->strField = $vv;
					$this->strInputName = $vv;
					$this->varValue = $objRow->$vv;

					// Convert CSV fields (see #2890)
					if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['multiple'] && isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['csv'])) {
						$this->varValue = \StringUtil::trimsplit($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['csv'], $this->varValue);
					}

					// Call load_callback
					if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['load_callback'])) {
						foreach ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['load_callback'] as $callback) {
							if (\is_array($callback)) {
								$this->import($callback[0]);
								$this->varValue = $this->{$callback[0]}->{$callback[1]}($this->varValue, $this);
							} elseif (\is_callable($callback)) {
								$this->varValue = $callback($this->varValue, $this);
							}
						}
					}

					// Re-set the current value
					$this->objActiveRecord->{$this->strField} = $this->varValue;

					// Build the row and pass the current palette string (thanks to Tristan Lins)
					$blnAjax ? $arrAjax[$thisId] .= $this->row($this->strPalette) : $return .= $this->row($this->strPalette);
				}

				$class = 'tl_box';
				$return .= "\n" . '</fieldset>';
			}
		}

		// Versions overview
		if ($GLOBALS['TL_DCA'][$this->strTable]['config']['enableVersioning'] && !$GLOBALS['TL_DCA'][$this->strTable]['config']['hideVersionMenu']) {
			$version = $objVersions->renderDropdown();
		} else {
			$version = '';
		}

		// Submit buttons
		$arrButtons = array();
		$arrButtons['save'] = '<button type="submit" name="save" id="save" class="tl_submit" accesskey="s">' . $GLOBALS['TL_LANG']['MSC']['save'] . '</button>';

		if (!\Input::get('nb')) {
			$arrButtons['saveNclose'] = '<button type="submit" name="saveNclose" id="saveNclose" class="tl_submit" accesskey="c">' . $GLOBALS['TL_LANG']['MSC']['saveNclose'] . '</button>';

			if (!\Input::get('nc')) {
				if (!$GLOBALS['TL_DCA'][$this->strTable]['config']['closed'] && !$GLOBALS['TL_DCA'][$this->strTable]['config']['notCreatable']) {
					$arrButtons['saveNcreate'] = '<button type="submit" name="saveNcreate" id="saveNcreate" class="tl_submit" accesskey="n">' . $GLOBALS['TL_LANG']['MSC']['saveNcreate'] . '</button>';

					if (!$GLOBALS['TL_DCA'][$this->strTable]['config']['notCopyable']) {
						$arrButtons['saveNduplicate'] = '<button type="submit" name="saveNduplicate" id="saveNduplicate" class="tl_submit" accesskey="d">' . $GLOBALS['TL_LANG']['MSC']['saveNduplicate'] . '</button>';
					}
				}

				if ($GLOBALS['TL_DCA'][$this->strTable]['config']['switchToEdit']) {
					$arrButtons['saveNedit'] = '<button type="submit" name="saveNedit" id="saveNedit" class="tl_submit" accesskey="e">' . $GLOBALS['TL_LANG']['MSC']['saveNedit'] . '</button>';
				}

				if ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 4 || \strlen($this->ptable) || $GLOBALS['TL_DCA'][$this->strTable]['config']['switchToEdit']) {
					$arrButtons['saveNback'] = '<button type="submit" name="saveNback" id="saveNback" class="tl_submit" accesskey="g">' . $GLOBALS['TL_LANG']['MSC']['saveNback'] . '</button>';
				}
			}
		}

		// Call the buttons_callback (see #4691)
		if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['edit']['buttons_callback'])) {
			foreach ($GLOBALS['TL_DCA'][$this->strTable]['edit']['buttons_callback'] as $callback) {
				if (\is_array($callback)) {
					$this->import($callback[0]);
					$arrButtons = $this->{$callback[0]}->{$callback[1]}($arrButtons, $this);
				} elseif (\is_callable($callback)) {
					$arrButtons = $callback($arrButtons, $this);
				}
			}
		}

		if (\count($arrButtons) < 3) {
			$strButtons = implode(' ', $arrButtons);
		} else {
			$strButtons = array_shift($arrButtons) . ' ';
			$strButtons .= '<div class="split-button">';
			$strButtons .= array_shift($arrButtons) . '<button type="button" id="sbtog">' . \Image::getHtml('navcol.svg') . '</button> <ul class="invisible">';

			foreach ($arrButtons as $strButton) {
				$strButtons .= '<li>' . $strButton . '</li>';
			}

			$strButtons .= '</ul></div>';
		}

		// Add the buttons and end the form
		$return .= '
</div>
<div class="tl_formbody_submit">
<div class="tl_submit_container">
  ' . $strButtons . '
</div>
</div>
</form>';

		$strVersionField = '';

		// Store the current version number (see #8412)
		if (($intLatestVersion = $objVersions->getLatestVersion()) !== null) {
			$strVersionField = '
<input type="hidden" name="VERSION_NUMBER" value="' . $intLatestVersion . '">';
		}

		// Begin the form (-> DO NOT CHANGE THIS ORDER -> this way the onsubmit attribute of the form can be changed by a field)
		$return = $version . \Message::generate() . ($this->noReload ? '
<p class="tl_error">' . $GLOBALS['TL_LANG']['ERR']['general'] . '</p>' : '') . '
<div id="tl_buttons">' . (\Input::get('nb') ? '&nbsp;' : '
<a href="' . $this->getReferer(true) . '" class="header_back" title="' . \StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']) . '" accesskey="b" onclick="Backend.getScrollOffset()">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a>') . '
</div>
<form action="' . ampersand(\Environment::get('request'), true) . '" id="' . $this->strTable . '" class="tl_form tl_edit_form" method="post" enctype="' . ($this->blnUploadable ? 'multipart/form-data' : 'application/x-www-form-urlencoded') . '"' . (!empty($this->onsubmit) ? ' onsubmit="' . implode(' ', $this->onsubmit) . '"' : '') . '>
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="' . $this->strTable . '">
<input type="hidden" name="REQUEST_TOKEN" value="' . REQUEST_TOKEN . '">' . $strVersionField . '
<input type="hidden" name="FORM_FIELDS[]" value="' . \StringUtil::specialchars($this->strPalette) . '">' . $return;

		// Reload the page to prevent _POST variables from being sent twice
		if (\Input::post('FORM_SUBMIT') == $this->strTable && !$this->noReload) {
			$arrValues = $this->values;
			array_unshift($arrValues, time());

			// Trigger the onsubmit_callback
			if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback'])) {
				foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback'] as $callback) {
					if (\is_array($callback)) {
						$this->import($callback[0]);
						$this->{$callback[0]}->{$callback[1]}($this);
					} elseif (\is_callable($callback)) {
						$callback($this);
					}
				}
			}

			// Set the current timestamp before adding a new version
			if ($GLOBALS['TL_DCA'][$this->strTable]['config']['dynamicPtable']) {
				$this->Database->prepare("UPDATE " . $this->strTable . " SET ptable=?, tstamp=? WHERE id=?")
					->execute($this->ptable, time(), $this->intId);
			} else {
				$this->Database->prepare("UPDATE " . $this->strTable . " SET tstamp=? WHERE id=?")
					->execute(time(), $this->intId);
			}

			// Save the current version
			if ($this->blnCreateNewVersion) {
				$objVersions->create();

				// Call the onversion_callback
				if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['onversion_callback'])) {
					@trigger_error('Using the onversion_callback has been deprecated and will no longer work in Contao 5.0. Use the oncreate_version_callback instead.', E_USER_DEPRECATED);

					foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['onversion_callback'] as $callback) {
						if (\is_array($callback)) {
							$this->import($callback[0]);
							$this->{$callback[0]}->{$callback[1]}($this->strTable, $this->intId, $this);
						} elseif (\is_callable($callback)) {
							$callback($this->strTable, $this->intId, $this);
						}
					}
				}
			}

			// Show a warning if the record has been saved by another user (see #8412)
			if ($intLatestVersion !== null && isset($_POST['VERSION_NUMBER']) && $intLatestVersion > \Input::post('VERSION_NUMBER')) {
				/** @var BackendTemplate|object $objTemplate */
				$objTemplate = new \BackendTemplate('be_conflict');

				$objTemplate->language = $GLOBALS['TL_LANGUAGE'];
				$objTemplate->title = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['versionConflict']);
				$objTemplate->theme = \Backend::getTheme();
				$objTemplate->charset = \Config::get('characterSet');
				$objTemplate->base = \Environment::get('base');
				$objTemplate->h1 = $GLOBALS['TL_LANG']['MSC']['versionConflict'];
				$objTemplate->explain1 = sprintf($GLOBALS['TL_LANG']['MSC']['versionConflict1'], $intLatestVersion, \Input::post('VERSION_NUMBER'));
				$objTemplate->explain2 = sprintf($GLOBALS['TL_LANG']['MSC']['versionConflict2'], $intLatestVersion + 1, $intLatestVersion);
				$objTemplate->diff = $objVersions->compare(true);
				$objTemplate->href = \Environment::get('request');
				$objTemplate->button = $GLOBALS['TL_LANG']['MSC']['continue'];

				throw new ResponseException($objTemplate->getResponse());
			}

			// Redirect
			if (isset($_POST['saveNclose'])) {
				\Message::reset();
				\System::setCookie('BE_PAGE_OFFSET', 0, 0);

				$this->redirect($this->getReferer());
			} elseif (isset($_POST['saveNedit'])) {
				\Message::reset();
				\System::setCookie('BE_PAGE_OFFSET', 0, 0);

				$this->redirect($this->addToUrl($GLOBALS['TL_DCA'][$this->strTable]['list']['operations']['edit']['href'], false, array('s2e', 'act', 'mode', 'pid')));
			} elseif (isset($_POST['saveNback'])) {
				\Message::reset();
				\System::setCookie('BE_PAGE_OFFSET', 0, 0);

				if ($this->ptable == '') {
					$this->redirect(TL_SCRIPT . '?do=' . \Input::get('do'));
				}
				// TODO: try to abstract this
				elseif (($this->ptable == 'tl_theme' && $this->strTable == 'tl_style_sheet') || ($this->ptable == 'tl_page' && $this->strTable == 'tl_article')) {
					$this->redirect($this->getReferer(false, $this->strTable));
				} else {
					$this->redirect($this->getReferer(false, $this->ptable));
				}
			} elseif (isset($_POST['saveNcreate'])) {
				\Message::reset();
				\System::setCookie('BE_PAGE_OFFSET', 0, 0);

				$strUrl = TL_SCRIPT . '?do=' . \Input::get('do');

				if (isset($_GET['table'])) {
					$strUrl .= '&amp;table=' . \Input::get('table');
				}

				// Tree view
				if ($this->treeView) {
					$strUrl .= '&amp;act=create&amp;mode=1&amp;pid=' . $this->intId;
				}

				// Parent view
				elseif ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 4) {
					$strUrl .= $this->Database->fieldExists('sorting', $this->strTable) ? '&amp;act=create&amp;mode=1&amp;pid=' . $this->intId : '&amp;act=create&amp;mode=2&amp;pid=' . $this->activeRecord->pid;
				}

				// List view
				else {
					$strUrl .= ($this->ptable != '') ? '&amp;act=create&amp;mode=2&amp;pid=' . CURRENT_ID : '&amp;act=create';
				}

				$this->redirect($strUrl . '&amp;rt=' . REQUEST_TOKEN);
			} elseif (isset($_POST['saveNduplicate'])) {
				\Message::reset();
				\System::setCookie('BE_PAGE_OFFSET', 0, 0);

				$strUrl = TL_SCRIPT . '?do=' . \Input::get('do');

				if (isset($_GET['table'])) {
					$strUrl .= '&amp;table=' . \Input::get('table');
				}

				// Tree view
				if ($this->treeView) {
					$strUrl .= '&amp;act=copy&amp;mode=1&amp;id=' . $this->intId . '&amp;pid=' . $this->intId;
				}

				// Parent view
				elseif ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 4) {
					$strUrl .= $this->Database->fieldExists('sorting', $this->strTable) ? '&amp;act=copy&amp;mode=1&amp;pid=' . $this->intId . '&amp;id=' . $this->intId : '&amp;act=copy&amp;mode=2&amp;pid=' . CURRENT_ID . '&amp;id=' . $this->intId;
				}

				// List view
				else {
					$strUrl .= ($this->ptable != '') ? '&amp;act=copy&amp;mode=2&amp;pid=' . CURRENT_ID . '&amp;id=' . CURRENT_ID : '&amp;act=copy&amp;id=' . CURRENT_ID;
				}

				$this->redirect($strUrl . '&amp;rt=' . REQUEST_TOKEN);
			}

			$this->reload();
		}

		// Set the focus if there is an error
		if ($this->noReload) {
			$return .= '
<script>
  window.addEvent(\'domready\', function() {
    Backend.vScrollTo(($(\'' . $this->strTable . '\').getElement(\'label.error\').getPosition().y - 20));
  });
</script>';
		}

		return $return;
	}
}
