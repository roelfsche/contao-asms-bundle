<?php

namespace vacancies;

/**
 * Reads and writes jobs
 *
 * @author    Stefan Becker (beckeste@beckeste.de)
 * @copyright Stefan Becker 2014
 */
class JobsModel extends \Model
{
    /** @var string $strTable Table name */
    protected static $strTable = 'tl_jobs';

   private static function buildBaseOptions(array $arrWhere=array(), array $arrWhereOpt=array(), array $arrOptions=array())
   {
       // Never return unpublished elements in the back end, so they don't end up in the RSS feed
       if (!BE_USER_LOGGED_IN || TL_MODE == 'BE')
       {
           $t = static::$strTable;
           $time = time();
           $arrWhere[] = "($t.start='' OR $t.start<$time)";
           $arrWhere[] = "($t.stop='' OR $t.stop>$time)";
           $arrWhere[] = "$t.published=1";
       }

       if (count($arrWhereOpt)) {
           $arrWhere[] = '(' . implode(' OR ', $arrWhere) . ')';
       }

        if (empty($arrWhere)) {
            $arrWhere = null;
        }

       $arrOptions = array_merge
       (
           array
           (
               'table'  => static::$strTable,
               'column' => $arrWhere,
               'value'  => null
           ),
           $arrOptions
       );

       return $arrOptions;
   }

    /**
     * Find published jobs
     *
     * @return \Model\Model|null A collection of models or null if there are no news
     */
    public static function findByAlias($alias)
    {
        $t = static::$strTable;

        // Never return unpublished elements in the back end, so they don't end up in the RSS feed
        if (!BE_USER_LOGGED_IN || TL_MODE == 'BE')
        {
            $t = static::$strTable;
            $time = time();
            $arrColumns[] = "($t.start='' OR $t.start<$time)";
            $arrColumns[] = "($t.stop='' OR $t.stop>$time)";
            $arrColumns[] = "$t.published=1";
        }
        $arrColumns[] = 'alias=?';
        $arrValues = array($alias);

        return static::findOneBy($arrColumns, $arrValues);
    }

    /**
     * Find published jobs
     *
     * @return \Model\Collection|null A collection of models or null if there are no news
     */
    public static function findPublished($intLimit=0, $intOffset=0, array $arrWhere=array(), array $arrWhereOpt=array(), array $arrOptions=array())
    {
        $arrOptions = self::buildBaseOptions($arrWhere, $arrWhereOpt, $arrOptions);

        if (!isset($arrOptions['order']))
        {
            $arrOptions['order']  = static::$strTable . '.tstamp DESC';
        }

        $arrOptions['limit']  = $intLimit;
        $arrOptions['offset'] = $intOffset;

        return static::find($arrOptions);
    }

    /**
     * Count published jobs
     *
     * @return \Model\Collection|null A collection of models or null if there are no news
     */
    public static function countPublished(array $arrWhere=array(), array $arrWhereOpt=array(), array $arrOptions=array())
    {
        $strQuery = static::buildCountQuery(self::buildBaseOptions($arrWhere, $arrWhereOpt, $arrOptions));

        return (int) \Database::getInstance()->prepare($strQuery)->execute($arrOptions['value'])->count;
    }
}
