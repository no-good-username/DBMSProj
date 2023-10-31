<?php
session_start();
include_once "../config.php";
if (!isset($_SESSION['user_email'])) {
  header("location: ../authentication/login.php");
}
?>
<?php include_once "header.php"; ?>

<body style="background-image: url('../../images/dashboard/background.jpg'); backdrop-filter: blur(8px);">
  <div class="wrapper">
    <a href="../dashboard/dash.php" class="ml-5">Home</a>
    <section class="users">
      <header>
        <div class="content">
          <?php
          $sql = mysqli_query($conn, "SELECT * FROM register WHERE user_email = '{$_SESSION['user_email']}'");
          if (mysqli_num_rows($sql) > 0) {
            $row = mysqli_fetch_assoc($sql);
          }
          ?>
          <div class="details">
            <span class="flex flex-row space-x-2">
              <img
                src="https://images.pexels.com/photos/4588052/pexels-photo-4588052.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1"
                alt="">
              <?php echo $row['user_name'] ?>
            </span>
            <!-- <p><?php echo $row['status']; ?></p> -->
          </div>
        </div>
        <!-- <a href="php/logout.php?logout_id=<?php echo $row['unique_id']; ?>" class="logout">Logout</a> -->
      </header>

      <div class="search">
        <span class="text">Select an user to start chat</span>
        <input type="text" placeholder="Enter name to search...">
        <button><i class="fas fa-search"></i></button>
      </div>
      <div class="users-list">

      </div>
    </section>
  </div>

  <script src="javascript/users.js"></script>

</body>

</html>