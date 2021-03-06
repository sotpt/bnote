<?php
/**
 * Data Access Class for rehearsal data.
 * @author matti
 *
 */
class ProbenData extends AbstractData {
	
	/**
	 * Build data provider.
	 */
	function __construct() {
		$this->fields = array(
			"id" => array("Probennummer", FieldType::INTEGER),
			"begin" => array("Beginn", FieldType::DATETIME),
			"end" => array("Ende", FieldType::DATETIME),
			"location" => array("Ort", FieldType::REFERENCE),
			"notes" => array("Notizen", FieldType::TEXT)
		);
		
		$this->references = array(
			"location" => "location", 
		);
		
		$this->table = "rehearsal";
		
		$this->init();
	}
	
	private function defaultQuery() {
		$query = "SELECT r.id as id, begin, end, r.notes as notes, name, street, city, zip";
		$query .= " FROM " . $this->table . " r, location l, address a";
		$query .= " WHERE r.location = l.id AND l.address = a.id";
		return $query;
	}
	
	function getNextRehearsal() {
		$query = $this->defaultQuery() . " AND begin > NOW()";
		$query .= " ORDER BY begin ASC LIMIT 0,1";
		return $this->database->getRow($query);
	}
	
	function getAllRehearsals() {
		return $this->adp()->getAllRehearsals();
	}
	
	function findByIdJoined($id, $colExchange) {
		$query = $this->defaultQuery() . " AND r.id = $id";
		return $this->database->getRow($query);
	}
	
	function getParticipants($rid) {
		$query = 'SELECT CONCAT_WS(" ", c.name, c.surname) as name, ';
		$query .= ' CASE ru.participate WHEN 1 THEN "ja" WHEN 2 THEN "vielleicht" ELSE "nein" END as participate, ru.reason';
		$query .= ' FROM rehearsal_user ru, user u, contact c';
		$query .= ' WHERE ru.rehearsal = ' . $rid . ' AND ru.user = u.id AND u.contact = c.id';
		$query .= ' ORDER BY participate, name';
		return $this->database->getSelection($query);
	}
	
	function getParticipantStats($rid) {
		$stats = array();
		
		// number of paricipants who...
		// ...paricipate
		$stats["Zusagen"] = $this->database->getCell("rehearsal_user", "count(*)", "rehearsal = $rid AND participate = 1");
		// ...do not paricipate
		$stats["Absagen"] = $this->database->getCell("rehearsal_user", "count(*)", "rehearsal = $rid AND participate = 0");
		// ...maybe participate
		$stats["Eventuell"] = $this->database->getCell("rehearsal_user", "count(*)", "rehearsal = $rid AND participate = 2");
		// total number of...
		// ...decisions
		$stats["Summe"] = $this->database->getCell("rehearsal_user", "count(*)", "rehearsal = $rid");
		// ...members
		//TODO $stats["Anzahl Mitspieler generell"] = $this->database->getCell("user", "count(*)", "");
		
		//TODO percentage of participants of total
		
		//TODO attending instruments
		
		return $stats;
	}
	
	function getRehearsalBegin($rid) {
		$d = $this->database->getCell($this->getTable(), "begin", "id = $rid");
		return Data::convertDateFromDb($d);
	}
	
	function getSongsForRehearsal($rid) {
		$query = "SELECT s.id, s.title, rs.notes ";
		$query .= "FROM rehearsal_song rs, song s ";
		$query .= "WHERE rs.song = s.id AND rs.rehearsal = $rid";
		return $this->database->getSelection($query);
	}
	
	function saveSongForRehearsal($sid, $rid, $notes) {
		$this->regex->isText($notes);
		$query = "INSERT INTO rehearsal_song (song, rehearsal, notes) VALUES ";
		$query .= "($sid, $rid, \"$notes\")";
		$this->database->execute($query);
	}
	
	function updateSongForRehearsal($sid, $rid, $notes) {
		$this->regex->isText($notes);
		$query = "UPDATE rehearsal_song SET ";
		$query .= " notes = \"$notes\" WHERE rehearsal = $rid AND song = $sid";
		$this->database->execute($query);
	}
	
	function removeSongForRehearsal($sid, $rid) {
		$query = "DELETE FROM rehearsal_song WHERE rehearsal = $rid AND song = $sid";
		$this->database->execute($query);
	}
}

?>