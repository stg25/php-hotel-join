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
    function getConfigurazioneId() {
      return $this->configurazioneId;
    }

    public static function getAllPrenotazioni($conn) {

      $sql = "
              SELECT *
              FROM prenotazioni
              WHERE MONTH(created_at) = 5
              ORDER BY created_at DESC

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
    function getId() {
      return $this->id;
    }
    function getRoomNumber() {
      return $this->roomNumber;
    }
    function getFloor() {
      return $this->floor;
    }
    function getBeds() {
      return $this->beds;
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

  class Configurazione {

    private $id;
    private $title;
    private $description;

    function __construct($id, $title, $description) {

      $this->id = $id;
      $this->title = $title;
      $this->description = $description;

    }

    function getTitle() {
      return $this->title;
    }
    function getDescription() {
      return $this->description;
    }

    public static function getConfigurazioneById($conn, $id) {

      $sql = "

              SELECT *
              FROM configurazioni
              WHERE id = $id

      ";

      $result = $conn->query($sql);

      if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $configurazione = new Configurazione( $row["id"],
                                              $row["title"],
                                              $row["description"]);
      }

      return $configurazione;
    }
  }

  class Pagamento {

    private $id;
    private $status;
    private $price;

    function __construct($id, $status, $price) {

      $this->id = $id;
      $this->status = $status;
      $this->price = $price;

    }

    function getId() {
      return $this->id;
    }
    function getStatus() {
      return $this->status;
    }
    function getPrice() {
      return $this->price;
    }

    public static function getPagamentoById($conn, $id) {

      $sql = "

              SELECT *
              FROM pagamenti
              WHERE prenotazione_id = $id

      ";

      $result = $conn->query($sql);

      if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $pagamento = new Pagamento( $row["id"],
                                    $row["status"],
                                    $row["price"]);
      }

      return $pagamento;
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

    $configurazioneId = $prenotazione->getConfigurazioneId();
    $configurazione = Configurazione::getConfigurazioneById($conn, $configurazioneId);

    $pagamentoId = $prenotazione->getId();
    $pagamento = Pagamento::getPagamentoById($conn, $pagamentoId);

    echo "Prenotazione: " . $prenotazione->getId() . "<br>" .
            "- Stanza: " . $stanza->getId() .
                " ; Number: " . $stanza->getRoomNumber() .
                " ; Floor: " . $stanza->getFloor() .
                " ; Beds: " . $stanza->getBeds() . "<br>" .
            "- Configurazione: " . $prenotazione->getConfigurazioneId() .
                " ; " . $configurazione->getTitle() .
                " ; " . $configurazione->getDescription() . "<br>" .
            "- Pagamento: " . $pagamento->getId() .
                " ; Status: " . $pagamento->getStatus() .
                " ; Price: " . $pagamento->getPrice() . " €" .

            "<br><br>"


         ;
  }
  
?>
