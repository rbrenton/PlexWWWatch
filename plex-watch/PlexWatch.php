<?php
require_once("PlexWatchWatched.php");
require_once("PlexWatchWatchedIterator.php");
require_once("PlexWatchUser.php");
require_once("PlexWatchUserDetails.php");
require_once("PlexWatchStatistics.php");

class PlexWatch {
    function __construct($dbpath, $grouped = false) {
        $dsn =  "sqlite:" . $dbpath;
        $this->_dbh = new PDO($dsn);

        $this->_grouped = $grouped;
    }

    public function watched() {
        return iterator_to_array($this->_watchedIterator(), false);
    }

    public function users() {
        $it = $this->_watchedIterator();

        foreach ($it as $watched) {
            $username = $watched->user;
            if (!isset($users[$username])) {
                $users[$username] = new PlexWatchUser($username, $this);
            }
            $users[$username]->addWatched($watched);
        }

        return array_values($users);
    }

    public function user($username) {
        $config = [];
        $config["user"] = $username;

        $user = new PlexWatchUserDetails($username, $this);

        $it = $this->_watchedIterator($config);
        foreach ($it as $watched) {
            $user->addWatched($watched);
        }

        return $user;
    }

    public function settings() {
        if (!$this->_settings) {
            $statement = $this->_dbh->prepare("SELECT json FROM config");
            $statement->execute();
            $this->_settings = json_decode($statement->fetchColumn(), true);
        }

        return $this->_settings;
    }

    public function userDisplayName ($name, $device = "") {
        if (!$this->_userDisplayNames) {
            $this->_userDisplayNames = $this->settings()["user_display"];
        }

        if ($device !== "") {
            if(isset($this->_userDisplayNames[$name . "+" . $device])) {
                return $this->_userDisplayNames[$name . "+" . $device];
            }
        }

        if (isset($this->_userDisplayNames[$name])) {
            return $this->_userDisplayNames[$name];
        }
        return $name;
    }

    public function statistics () {
        $statistics = new PlexWatchStatistics();

        $it = $this->_watchedIterator();
        foreach ($it as $watched) {
            $statistics->addWatched($watched);
        }

        return $statistics;
    }

    private function _watchedIterator($config = []) {
        $bind = [];
        $where = [];

        $sql = "SELECT * FROM " . $this->_getWatchedTable();
        if (isset($config["user"])) {
            $where[] = ["user", ":user"];
            $bind[] = [":user", $config["user"], PDO::PARAM_STR];
        }

        if (!empty($where)) {
            $sql .= " WHERE ";
            foreach ($where as $w) {
                $sql .= " " . $w[0] . "=" . $w[1] . " ";
            }
        }

        $sql .= " ORDER BY id DESC";

        $statement = $this->_dbh->prepare($sql);

        foreach ($bind as $b) {
            $statement->bindValue($b[0], $b[1], $b[2]);
        }

        return new PlexWatchWatchedIterator($statement, $this);
    }

    private function _getWatchedTable () {
        if ($this->_grouped) {
            return "grouped";
        }
        return "processed";
    }

    private $_dbh;
    private $_grouped;
    private $_userDisplayNames;
    private $_settings;
}


?>
