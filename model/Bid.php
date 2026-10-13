<?php

use LDAP\Result;

require_once "framework/Model.php";

class Bid extends Model {

    public function __construct(
        private int $owner, 
        private int $item_id, 
        private DateTime $created_at, 
        private float $amount, 
        private string $owner_pseudo
    ) {}

    public function get_owner(): int {
        return $this->owner;
    }

    public function get_item_id(): int {
        return $this->item_id;
    }

    public function get_created_at(): DateTime {
        return $this->created_at;
    }

    public function get_amount(): float {
        return $this->amount;
    }

    public function get_owner_pseudo(): string {
        return $this->owner_pseudo;
    }

    public static function get_user_bids_by_id(int $user_id): ?array {
        $query = self::execute("SELECT item
                                FROM bids
                                WHERE bids.owner = :user_id", ["user_id" => $user_id]);
        $data = $query->fetchAll(PDO::FETCH_COLUMN);
        return $data ? $data : [];
    }

    public static function get_winning_bid_by_item_id(int $item_id): ?Bid {
        $query = self::execute("SELECT *, users.pseudo
                                FROM bids
                                JOIN users on users.id = bids.owner
                                WHERE bids.item = :item_id
                                ORDER BY bids.amount DESC
                                LIMIT 1;", ["item_id" => $item_id]);
        $data = $query->fetch(PDO::FETCH_ASSOC);
        return $data ? self::build($data) : null;
    }

    public static function get_bids_by_item(int $item_id): array{
        $results = [];

        $query = self::execute("SELECT bids.*, users.pseudo
                                FROM bids
                                JOIN users on bids.owner = users.id
                                WHERE bids.item = :item_id
                                ORDER BY bids.created_at DESC", ["item_id" => $item_id]);
        $data = $query->fetchAll(PDO::FETCH_ASSOC);

        foreach ($data as $row) {
            $results[] = self::build($row);
        }
        return $results;
    }

    public static function add(int $bidder_id, int $item_id, float $amount) : void{
        $sql = "INSERT INTO bids (item, owner, amount, created_at) VALUES (:item, :owner, :amount, NOW())";

        self::execute($sql, [
            "item" => $item_id,
            "owner" => $bidder_id,
            "amount" => $amount
        ]);
    }

    private static function build(array $data) : Bid {
        return new Bid($data["owner"],
                        $data["item"],
                        new DateTime($data["created_at"]),
                        $data["amount"],
                        $data["pseudo"]);
    }

    public function validate(int $user_id): array {
        $errors = [];

        $item = Item::get_item_by_id($this->item_id);
        $bids = self::get_bids_by_item($this->item_id);

        if (!$item) {
            $errors[] = "Item not found.";
            return $errors;
        }

        $current_ref_price = (count($bids) > 0) ? $bids[0]->get_amount() : $item->get_starting_bid(); 

        if ($this->amount <= 0) {
            $errors[] = "Bid amount must be positive.";
        }

        if (count($bids) == 0) {
            if ($this->amount < $item->get_starting_bid()) {
                $errors[] = "Bid must be at least the starting price (€ ". number_format($item->get_starting_bid(), 2) . ").";
            }
        } else {
            if ($this->amount <= $current_ref_price) {
                $errors[] = "Bid must be higher than the current bid (€ ". number_format($current_ref_price, 2) . ").";
            }
        }
        
        if ($item->get_owner() == $user_id) {
            $errors[] = "You cannot bid on your own item.";
        }
        
        if (count($bids) > 0 && $bids[0]->get_owner() == $user_id) {
            $errors[] = "You are already the highest bidder.";
        }

        $now = new DateTime(AppTime::get_current_datetime());
        if ($now > $item->get_end_at() || $item->get_buy_now_reached()) {
            $errors[] = "This auction is closed.";
        }

        return $errors;
    }

    public function persist(): void {
        $sql = "INSERT INTO bids (item, owner, amount, created_at) 
                VALUES (:item, :owner, :amount, :created_at)";

        self::execute($sql, [
            "item"       => $this->item_id,
            "owner"      => $this->owner,
            "amount"     => $this->amount,
            "created_at" => $this->created_at->format('Y-m-d H:i:s')
        ]);
    }
}