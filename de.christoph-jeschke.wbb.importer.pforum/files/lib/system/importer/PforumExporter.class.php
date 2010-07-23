<?php
/**
 *  Powie's Forum Exporter
 *
 *  PHP Version 5.2.0
 *
 *  @category WBB
 *  @package  de.christoph-jeschke.wbb.importer.pforum
 *  @author   Christoph Jeschke <tools@christoph-jeschke.de>
 *  @license  Copyright protected
 *  @version  $Id$
 *  @link     http://christoph-jeschke.de/
 */

require_once WBB_DIR.'lib/system/importer/Exporter.class.php';

/**
 *  Powie's Forum Exporter Class
 *
 *  @category WBB
 *  @package  de.christoph-jeschke.wbb.importer.pforum
 *  @author   Christoph Jeschke <tools@christoph-jeschke.de>
 *  @license  Copyright protected
 *  @link     http://christoph-jeschke.de/
 */
class PforumExporter extends Exporter
{
    public $useDatabase              = true;
    public $supportedDatabaseClasses = array('MySQLDatabase', 'MySQLiDatabase');

    public $supportedData = array(
        'users'                 => 1,
        'threads'               => 1,
        'boards'                => 1,
        'groups'                => 0,
        'avatars'               => 0,
        'moderators'            => 0,
        'boardSubscriptions'    => 0,
        'threadRatings'         => 0,
        'attachments'           => 0,
        'threadSubscriptions'   => 0,
        'polls'                 => 0,
        'privateMessages'       => 0,
        'privateMessageFolders' => 0,
        'boardPermissions'      => 0,
        'smilies'               => 0,
        'userOptions'           => 0,
        'calendars'             => 0,
        'calendarEvents'        => 0
    );

    /**
     *  Class constructor
     *
     *  @return object
     */
    public function __construct()
    {
        $this->settings['tablePrefix'] = '';
        $this->settings['sourcePath']  =
            (!empty($_SERVER['DOCUMENT_ROOT']) ?
                $_SERVER['DOCUMENT_ROOT'] : WCF_DIR);
    }

    /**
     *  Validate Settings
     *
     *  @return true
     */
    public function validate()
    {
        parent::validate();

        // set table prefix
        $this->settings['tablePrefix'] = $this->settings['tablePrefix'];


        try {
            $this->getDB()->sendQuery("SELECT COUNT(*) FROM ".
                $this->dbPrefix.
                $this->settings['tablePrefix']."pfpost");
        }
        catch (Exception $e) {
            throw new UserInputException('tablePrefix', 'invalid');
        }

        // source path
        $tmpPath = FileUtil::addTrailingSlash($this->settings['sourcePath']);
        if ($this->data['avatars']) {
            if (!@file_exists($tmpPath.'moderate.php')) {
                $tmpPath = FileUtil::addTrailingSlash(dirname($tmpPath));
                if (!@file_exists($tmpPath.'moderate.php')) {
                    throw new UserInputException('sourcePath', 'invalid');
                }
            }
        }

        $this->settings['sourcePath'] = $tmpPath;

        return true;
    }

    /**
     *  Count users in Database
     *
     *  @see Exporter::countUsers()
     *  @return int Amount of users in Database
     */
    public function countUsers()
    {
        $sql = sprintf('SELECT COUNT(*) AS amount FROM %s%s%s',
                $this->dbPrefix,
                $this->settings['tablePrefix'],
                'pfuser');

        $row = $this->getDB()->getFirstRow($sql);
        return $row['amount'];
    }

    /**
     *  Count boards in Database
     *
     *  @see Exporter::countBoards()
     *  @return int Amount of boards in Database
     */
    public function countBoards()
    {
        $sql = sprintf('SELECT COUNT(*) AS amount FROM %s%s%s',
                $this->dbPrefix,
                $this->settings['tablePrefix'],
                'pfboard');

        $row = $this->getDB()->getFirstRow($sql);
        return $row['amount'];
    }

    /**
     *  Count threads in Database
     *
     *  @see Exporter::countThreads()
     *  @return int Amount of threads in Database
     */
    public function countThreads()
    {
        $sql = sprintf('SELECT COUNT(*) AS amount FROM %s%s%s',
                $this->dbPrefix,
                $this->settings['tablePrefix'],
                'pfthread');

        $row = $this->getDB()->getFirstRow($sql);
        return $row['amount'];
    }

    /**
     *  Count posts in Database
     *
     *  @see Exporter::countPosts()
     *  @return int Amount of posts in Database
     */
    public function countPosts()
    {
        $sql = sprintf('SELECT COUNT(*) AS amount FROM %s%s%s',
                $this->dbPrefix,
                $this->settings['tablePrefix'],
                'pfpost');

        $row = $this->getDB()->getFirstRow($sql);
        return $row['amount'];
    }

    /**
     *  Export user data from pforum database
     *
     *  @param int $offset Selection Offset
     *  @param int $limit  Selection Limit
     *  @return true
     *  @see Exporter::exportUsers()
     */
    public function exportUsers($offset, $limit)
    {
        $sql = sprintf('SELECT * FROM %s WHERE id > 1',
                        $this->dbPrefix.$this->settings['tablePrefix'].
                        'pfuser');

        $res = $this->getDB()->sendQuery($sql, $limit, $offset);

        while ($row = $this->getDB()->fetchArray($res)) {
            $user = array(  'signature'             => $row['signatur'],
                            'registrationDate'      => (int) $row['joined'],
                            'lastActivityTime'      => (int) $row['lastlogin'],
                            'registrationIpAddress' => $row['ip']);

            $options = array(   'homepage'  => $row['homepage'],
                                'icq'       => $row['icq'],
                                'aim'       => $row['aimid'],
                                'msn'       => $row['msnid']);

            $this->getImporter()->importUser($row['id'],
                $row['username'],
                $row['email'],
                md5(date('c')),
                array(0), $options, $user);
        }

        return true;
    }

    /**
     *  Export board data from pforum database
     *
     *  @param int $offset Selection Offset
     *  @param int $limit  Selection Limit
     *  @return true
     *  @see Exporter::exportBoards()
     */
    public function exportBoards($offset, $limit)
    {
        $sql = sprintf('SELECT id, name, info, catid FROM %s',
                $this->dbPrefix.$this->settings['tablePrefix'].
                'pfboard');

        $result = $this->getDB()->sendQuery($sql, $limit, $offset);

        while ($row = $this->getDB()->fetchArray($result)) {
            $data = array();
            $this->getImporter()->importBoard($row['id'],
                $row['catid'],
                0,
                addslashes($row['name']),
                addslashes($row['info']),
                $data);
        }

        return true;
    }

    /**
     *  Export threads from pforum database
     *
     *  @param int $offset Selection Offset
     *  @param int $limit  Selection Limit
     *  @return true
     *  @see   Exporter::exportThreads()
     */
    public function exportThreads($offset, $limit)
    {
        $sql = sprintf('SELECT id, count, boardid, userid, titel, '.
                'time, name, starttime, postcount FROM %s',
                $this->dbPrefix.$this->settings['tablePrefix'].
                'pfthread');

        $result = $this->getDB()->sendQuery($sql, $limit, $offset);

        while ($row = $this->getDB()->fetchArray($result)) {
            $data = array(  'views'        => $row['count'],
                            'lastPostTime' => $row['time'],
                            'lastPosterID' => $row['userid'],);

            $this->getImporter()->importThread($row['id'],
                $row['boardid'],
                $row['titel'],
                $row['name'],
                $row['time'],
                null);
        }

        return true;
    }

    /**
     *  Export posts from pforum database
     *
     *  @param int $offset Selection Offset
     *  @param int $limit  Selection Limit
     *  @return true
     *  @see Exporter::exportPosts()
     */
    public function exportPosts($offset, $limit)
    {
        $sql = sprintf('SELECT id, host, usesmile, threadid, userid, ' .
                'name, time, post FROM %s',
                $this->dbPrefix.$this->settings['tablePrefix'].
                'pfpost');

        $result = $this->getDB()->sendQuery($sql, $limit, $offset);

        while ($row = $this->getDB()->fetchArray($result)) {
            $data = array('enableSmilies' => intval($row['usesmile']),
                'ipAddress' => $row['host']);

            $this->getImporter()->importPost($row['id'],
                $row['threadid'],
                $row['userid'],
                $row['name'],
                $row['time'],
                '',
                $row['post'],
                $data);
        }

        return true;
    }
}
?>
