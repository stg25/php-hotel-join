<?php

  include "databaseInfo.php";

  class Prenotazione {

    public $id;
    public $stanzaId;
    public $configurazioneId;
    public $createdAt;

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
              SELECT id, stanza_id, configurazione_id, created_at
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

    public $id;
    public $roomNumber;
    public $floor;
    public $beds;

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

              SELECT id, room_number, floor, beds
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

    public $id;
    public $title;
    public $description;

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

              SELECT id, title, description
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

    public $id;
    public $status;
    public $price;

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

              SELECT id, status, price
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

  class Ospite {

    public $id;
    public $name;
    public $lastname;

    function __construct($id, $name, $lastname) {

      $this->id = $id;
      $this->name = $name;
      $this->lastname = $lastname;

    }

    function getName() {
      return $this->name;
    }
    function getLastname() {
      return $this->lastname;
    }

    public static function getOspiteById($conn, $id) {

      $sql = "

              SELECT prenotazioni.id, ospiti.name, ospiti.lastname
              FROM prenotazioni
              JOIN prenotazioni_has_ospiti
              ON prenotazioni.id = prenotazioni_has_ospiti.prenotazione_id
              JOIN ospiti
              ON prenotazioni_has_ospiti.ospite_id = ospiti.id
              WHERE prenotazioni.id = $id

      ";

      $result = $conn->query($sql);
      $ospiti = [];

      if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
          $ospite = new Ospite( $row["id"],
                                $row["name"],
                                $row["lastname"]);

          $ospiti[] = $ospite;
        };
      }

      return $ospiti;
    }
  }

  $conn = new mysqli($servername, $username, $password, $dbname);

  if ($conn->connect_errno) {

    echo $conn->connect_error;
    return;
  }

  $prenotazioni = Prenotazione::getAllPrenotazioni($conn);

  $response = [

  ];

  foreach ($prenotazioni as $prenotazione) {

    $stanzaId = $prenotazione->getStanzaId();
    $stanza = Stanza::getStanzaById($conn, $stanzaId);

    $configurazioneId = $prenotazione->getConfigurazioneId();
    $configurazione = Configurazione::getConfigurazioneById($conn, $configurazioneId);

    $pagamentoId = $prenotazione->getId();
    $pagamento = Pagamento::getPagamentoById($conn, $pagamentoId);

    $ospiteId = $prenotazione->getId();
    $ospiti = Ospite::getOspiteById($conn, $ospiteId);

    // Create obj for json_encode
    $prenotazione->stanza = $stanza;
    $prenotazione->configurazione = $configurazione;
    $prenotazione->pagamento = $pagamento;
    $prenotazione->ospiti = $ospiti;
    $response[] = $prenotazione;

    // Create echo with multiple ospiti

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
                " ; Price: " . $pagamento->getPrice() . " € <br>" .
            "- Ospiti: ";

    foreach ($ospiti as $ospite) {
      echo $ospite->getName() . " " . $ospite->getLastname() . ", ";
    }

    echo "<br><br>";


  }

  // echo json_encode($response);

?>
