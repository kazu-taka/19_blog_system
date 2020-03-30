<?php

require_once('config.php');

function getPostFindById($id)
{
  $dbh = connectDb();
  $sql = <<<SQL
  select
    p.*,
    c.name
  from
    posts p
  left join
    categories c
  on 
    p.category_id = c.id
  where
    p.id = :id
  SQL;

  $stmt = $dbh->prepare($sql);
  $stmt->bindParam(":id", $id, PDO::PARAM_INT);
  $stmt->execute();

  return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getPostsFindByCategoryId($category_id)
{
  $dbh = connectDb();

  $sql = <<<SQL
select
  p.*,
  c.name,
  u.name as user_name
from
  posts p
left join
  categories c
on p.category_id = c.id
left join
  users u
on p.user_id = u.id
SQL;

  if (
    isset($category_id) &&
    is_numeric($category_id)
  ) {
    $sql_where = " where p.category_id = :category_id";
  } else {
    $sql_where = "";
  }

  $sql_order = " order by p.created_at desc";

  // SQL結合
  $sql = $sql . $sql_where . $sql_order;

  $stmt = $dbh->prepare($sql);
  if (
    isset($category_id) &&
    is_numeric($category_id)
  ) {
    $stmt->bindParam(":category_id", $category_id, PDO::PARAM_INT);
  }
  $stmt->execute();
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function inputChkPost($post_param)
{
  $title = $post_param['title'];
  $body = $post_param['body'];
  $category_id = $post_param['category_id'];

  $errors = [];

  if ($title == '') {
    $errors[] = 'タイトルが未入力です';
  }
  if ($category_id == '') {
    $errors[] = 'カテゴリーが未選択です';
  }
  if ($body == '') {
    $errors[] = '本文が未入力です';
  }
  return $errors;
}

function insertPost($post_param)
{
  $title = $post_param['title'];
  $body = $post_param['body'];
  $category_id = $post_param['category_id'];
  $user_id = $_SESSION['id'];

  $dbh = connectDb();
  $sql = "insert into posts " .
    "(title, body, category_id, user_id, created_at, updated_at) values " .
    "(:title, :body, :category_id, :user_id, now(), now())";
  $stmt = $dbh->prepare($sql);

  $stmt->bindParam(':title', $title, PDO::PARAM_STR);
  $stmt->bindParam(':body', $body, PDO::PARAM_STR);
  $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
  $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

  $stmt->execute();
  return $dbh->lastInsertId();
}

function updatePost($post_param)
{
  $id = $post_param['id'];
  $title = $post_param['title'];
  $body = $post_param['body'];
  $category_id = $post_param['category_id'];

  $dbh = connectDb();
  $sql = <<<SQL
  update
    posts
  set
    title = :title,
    body = :body,
    category_id = :category_id
  where
    id = :id
  SQL;
  $stmt = $dbh->prepare($sql);

  $stmt->bindParam(':title', $title, PDO::PARAM_STR);
  $stmt->bindParam(':body', $body, PDO::PARAM_STR);
  $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
  $stmt->bindParam(':id', $id, PDO::PARAM_INT);

  $stmt->execute();

}