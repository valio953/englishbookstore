﻿<?php

class Books {
    
    // DB credentials
    private $host      = "localhost";
    private $user      = "root";
    private $pass      = "123456";
    private $dbname    = "englishbookstore";
 
    private $dbh;
    
    public function __construct(){
        // Set DSN
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname;
        
        // Create a new PDO instanace
        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
        }
        catch (PDOException $e) {
        
            echo $e->getMessage();
        }
    }
    
    public function test_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
    
    /* Admin function: Looking for book into GoodReads Database by book's ISBN
     * Using GoodReads API */
    public function admin_check_book_isbn($params) {
        // GoodReads API app key
        $GRkey = "cdNKGwcNSIFRq8lIqPQW8Q";
        $isbn = $params["isbn"];
        
        $grapi_src = file_get_contents("https://www.goodreads.com/search/index.xml?key=" . $GRkey . "&q=" . $isbn);

        $grapi_xml_str = simplexml_load_string($grapi_src,'SimpleXMLElement',LIBXML_NOCDATA);
        $grapi_json = json_encode($grapi_xml_str);
        $grapi_array = json_decode($grapi_json,TRUE);
       
        $btitle = $grapi_array["search"]["results"]["work"]["best_book"]["title"];
        $bauthor = $grapi_array["search"]["results"]["work"]["best_book"]["author"]["name"];
        
        $result = array("title" => $btitle, "author" => $bauthor);
        return $result;
    }
    
    /* Admin function: Get specific book's details from GoodReads DB and store them into local DB
     * Use GoodReads API */
    public function admin_add_book($params) {
        // GoodReads API app key
        $GRkey = "cdNKGwcNSIFRq8lIqPQW8Q";
        $isbn = $params["isbn"];
        $bcategory = $params["category"];
        $price = $params["price"];
        
        $grapi_src = file_get_contents("https://www.goodreads.com/book/isbn/" . $isbn . "?key=" . $GRkey);

        $grapi_xml_str = simplexml_load_string($grapi_src,'SimpleXMLElement',LIBXML_NOCDATA);
        $grapi_json = json_encode($grapi_xml_str);
        $grapi_array = json_decode($grapi_json,TRUE);
        
        $btitle = $grapi_array["book"]["title"];
        $bdescription = $grapi_array["book"]["description"];
        $bimg = $grapi_array["book"]["image_url"];
        $rst_addbook = $this->dbh->prepare('
            INSERT INTO
            books
            (
                book_isbn,
                book_category,
                book_title,
                book_description,
                book_img,
                book_price,
                book_regdate
              )
            VALUES
            (
                :isbn,
                :category,
                :title,
                :description,
                :img,
                :price,
                NOW()
            )
        ');
        $rst_addbook->execute(array(
            ":isbn" => $isbn,
            ":category" => $bcategory,
            ":title" => $btitle,
            ":description" => $bdescription,
            ":img" => $bimg,
            ":price" => $price
        ));
        
        
        if(isset($grapi_array["book"]["authors"]["author"][0]))
        {
            $prepare_authors = $grapi_array["book"]["authors"]["author"];
            $count_authors = count($prepare_authors);
            foreach($prepare_authors as $author)
            {
                if(empty($author["role"]) || $author["role"] === "Writer")
                {
                    $bauthor = $author["name"];
                    $bauthor_gr_id = $author["id"];
                    
                    $rst_addauthor = $this->dbh->prepare('
                        INSERT INTO
                        authors
                        (
                            author_gr_id,
                            author_name
                          )
                        VALUES
                        (
                            :gr_id,
                            :name
                        )
                    ');
                    $rst_addauthor->execute(array(
                        ":gr_id" => $bauthor_gr_id,
                        ":name" => $bauthor
                    ));
                    
                    $rst_connect_authors_book = $this->dbh->prepare('
                        INSERT INTO
                        authors_books
                        (
                            ab_author,
                            ab_book
                          )
                        VALUES
                        (
                            :author,
                            :book
                        )
                    ');
                    $rst_connect_authors_book->execute(array(
                        ":author" => $bauthor_gr_id,
                        ":book" => $isbn
                    ));
                }
            }
        } else {
            $bauthor = $grapi_array["book"]["authors"]["author"]["name"];
            $bauthor_gr_id = $grapi_array["book"]["authors"]["author"]["id"];
            
            $rst_addauthor = $this->dbh->prepare('
                INSERT INTO
                authors
                (
                    author_gr_id,
                    author_name
                  )
                VALUES
                (
                    :gr_id,
                    :name
                )
            ');
            $rst_addauthor->execute(array(
                ":gr_id" => $bauthor_gr_id,
                ":name" => $bauthor
            ));
            
            $rst_connect_authors_book = $this->dbh->prepare('
                INSERT INTO
                authors_books
                (
                    ab_author,
                    ab_book
                  )
                VALUES
                (
                    :author,
                    :book
                )
            ');
            $rst_connect_authors_book->execute(array(
                ":author" => $bauthor_gr_id,
                ":book" => $isbn
            ));
        }        
        
        return true;
    }
    
    public function get_books() {
        $rst_get_books = $this->dbh->prepare('
            SELECT
                book_isbn,
                book_quantity,
                book_category,
                book_title,
                book_description,
                book_img,
                book_reserved,
                book_reservation_date,
                book_reservation_name,
                book_reservation_email,
                book_price
            FROM
                books
        ');
        $rst_get_books->execute();
        $books = $rst_get_books->fetchAll(PDO::FETCH_ASSOC);
        
        $i = 0;
        foreach($books as $book) {
            $book_isbn = $book["book_isbn"];
            
            $rst_get_author = $this->dbh->prepare('
                SELECT
                    authors.author_name AS author_name,
                    ab_book
                FROM
                    authors_books
                    LEFT JOIN authors ON authors.author_gr_id = authors_books.ab_author
                WHERE
                    ab_book=:book_isbn
            ');
            $rst_get_author->execute(array(":book_isbn" => $book_isbn));
            $authors = $rst_get_author->fetchAll(PDO::FETCH_ASSOC);
            $count_bookauthors = count($authors);
            if($count_bookauthors > 1)
            {
                $author_name = "";
                for($an=0; $an<$count_bookauthors; $an++)
                {
                    if($an == ($count_bookauthors - 1))
                    {
                        $author_name .= $authors[$an]["author_name"];
                    }
                    else {
                        $author_name .= $authors[$an]["author_name"] . ", ";
                    }
                }
                $books[$i]["book_author"] = $author_name;
            }
            else {
                $author_name = $authors[0]["author_name"];
                $books[$i]["book_author"] = $author_name;
            }
            $i++;
        }
        
        return $books;
    }
    
    public function get_last_books() {
        $rst_get_books = $this->dbh->prepare('
            SELECT
                book_isbn,
                book_quantity,
                book_category,
                book_title,
                book_description,
                book_img,
                book_reserved,
                book_reservation_date,
                book_reservation_name,
                book_reservation_email,
                book_price
            FROM
                books
            ORDER BY
                book_regdate DESC
            LIMIT 5
        ');
        $rst_get_books->execute();
        $books = $rst_get_books->fetchAll(PDO::FETCH_ASSOC);
        
        $i = 0;
        foreach($books as $book) {
            $book_isbn = $book["book_isbn"];
            
            $rst_get_author = $this->dbh->prepare('
                SELECT
                    authors.author_name AS author_name,
                    ab_book
                FROM
                    authors_books
                    LEFT JOIN authors ON authors.author_gr_id = authors_books.ab_author
                WHERE
                    ab_book=:book_isbn
            ');
            $rst_get_author->execute(array(":book_isbn" => $book_isbn));
            $authors = $rst_get_author->fetchAll(PDO::FETCH_ASSOC);
            $count_bookauthors = count($authors);
            if($count_bookauthors > 1)
            {
                $author_name = "";
                for($an=0; $an<$count_bookauthors; $an++)
                {
                    if($an == ($count_bookauthors - 1))
                    {
                        $author_name .= $authors[$an]["author_name"];
                    }
                    else {
                        $author_name .= $authors[$an]["author_name"] . ", ";
                    }
                }
                $books[$i]["book_author"] = $author_name;
            }
            else {
                $author_name = $authors[0]["author_name"];
                $books[$i]["book_author"] = $author_name;
            }
            $i++;
        }
        
        return $books;
    }
    
    public function get_categories($params = null)
    {
        $rst_get_categories = $this->dbh->prepare('
            SELECT
                category_id,
                category_name
            FROM
                categories
        ');
        $rst_get_categories->execute();
        $categories = $rst_get_categories->fetchAll(PDO::FETCH_ASSOC);
        
        return $categories;
    }
    
    public function admin_remove_book($params)
    {
        $isbn = $params['isbn'];
        
        $rst_remove_book = $this->dbh->prepare('
            DELETE
                FROM
                    books
            WHERE
                book_isbn=:isbn
        ');
        $rst_remove_book->execute(array(":isbn" => $isbn));
        
        return true;
    }
    
    /* User script */
    public function reserve_book($params)
    {
        $isbn = $params["isbn"];
        $reservation_name = $this->test_input($params["rname"]);
        $reservation_email = $this->test_input($params["remail"]);
        
       // var_dump($reservation_name);
        //var_dump($params); die;
        $rst_reserve_book = $this->dbh->prepare('
            UPDATE
                books
            SET
                book_reserved="yes",
                book_reservation_date=NOW(),
                book_reservation_name=:rname,
                book_reservation_email=:remail
            WHERE
                book_isbn=:isbn
        ');
        $rst_reserve_book->execute(array(
                                        ":rname" => $reservation_name,
                                        ":remail" => $reservation_email,
                                        ":isbn" => $isbn
        ));
        
        return "This book was reservered for you for 2 DAYS!";
    }
}