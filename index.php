<?php

  include "databaseInfo.php";

  class Prenotazione {

    private $id;
    private $stanzaId;
    private $configurazioneId;
    private $createdAt;

    function __construct($id, $stanzaId, $configurazioneId, $createdAt) {

      $this->id = $id;
      $this->stanzaId = $stanzaId;
      $this->configurazioneId = $configurazioneId;
      $this->createdAt = $createdAt;
    }

    function getId() {
      return $this->id;
    }
    function getStanzaId() {
      return $this->stanzaId;
    }

    public static function getAllPrenotazioni($conn) {

      $sql = "
              SELECT *
              FROM prenotazioni
              WHERE MONTH(created_at) = 5

      ";

      $result = $conn->query($sql);

      if ($result->num_rows > 0) {
        $prenotazioni = [];
        while ($row = $result->fetch_assoc()) {
          $prenotazioni[] =
            new Prenotazione( $row["id"],
                              $row["stanza_id"],
                              $row["configurazione_id"],
                              $row["created_at"]);
        }
      }

      return $prenotazioni;
    }
  }

  class Stanza {

    private $id;
    private $roomNumber;
    private $floor;
    private $beds;

    function __construct($id, $roomNumber, $floor, $beds) {

      $this->id = $id;
      $this->roomNumber = $roomNumber;
      $this->floor = $floor;
      $this->beds = $beds;

    }

    function getRoomNumber() {
      return $this->roomNumber;
    }
    function getFloor() {
      return $this->floor;
    }

    public static function getStanzaById($conn, $id) {

      $sql = "

              SELECT *
              FROM stanze
              WHERE id = $id

      ";

      $result = $conn->query($sql);

      if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stanza = new Stanza( $row["id"],
                              $row["room_number"],
                              $row["floor"],
                              $row["beds"]);
      }

      return $stanza;
    }
  }

  $conn = new mysqli($servername, $username, $password, $dbname);

  if ($conn->connect_errno) {

    echo $conn->connect_error;
    return;
  }

  $prenotazioni = Prenotazione::getAllPrenotazioni($conn);

  foreach ($prenotazioni as $prenotazione) {

    $stanzaId = $prenotazione->getStanzaId();
    $stanza = Stanza::getStanzaById($conn, $stanzaId);

    echo "ID prenotazione: " . $prenotazione->getId() . "<br>" .
         "Numero stanza: " . $stanza->getRoomNumber() . "<br>" .
         "Piano stanza: " . $stanza->getFloor() . "<br><br>";
  }











































?>
