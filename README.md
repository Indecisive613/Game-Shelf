# Game Shelf

## Overview
Game Shelf is a website that helps users keep track of their board game collection. Users add games to a virtual board game shelf, then search their shelf using various criteria such as game category, creator, and player count to find specific games. Users can choose how they want the results to be displayed: alphabetically, by complexity, by game length, or by popularity. The site also includes a game library containing board games from all users, which can be browsed or searched to discover new games that suit a user’s tastes. Finally, the friends feature allows users to see the games of others. This can be used as another game recommendation tool, or if the users know each other in person, this can help determine which games they should play together.

## Languages  
This project uses HTML, CSS, PHP and SQL. Since Github only allows for static sites, a working product is not available. Instead, please see the detailed description which contains screenshots of the site.

## Description  
After logging in, the user is brought to the homepage of Game Shelf. 
![image](https://user-images.githubusercontent.com/83597131/174108200-cb0b179f-8603-4b11-be82-ca1f8e8196a6.png)  
(Screenshot of login.php)  

The website is organized into 5 main sections accessible in the navigation bar found at the top of every page.  
![image](https://user-images.githubusercontent.com/83597131/174106744-561fd1bb-b9eb-4684-9640-4a2913f0cfd3.png)  
(Screenshot of index.php)  

In "My Shelf" a user can add a game to their shelf by typing the name of the game into the textbox. If the game already exists, it will be added to the users shelf. If it is a new game, the user will be asked to fill out some information about the game. Checks are performed on the inputted information to ensure the information makes sense.  
![image](https://user-images.githubusercontent.com/83597131/174163022-8c3dbe97-314c-4b56-a563-e1fa8c8344c3.png)  
(Screenshot of add_game1.php)  
![image](https://user-images.githubusercontent.com/83597131/174164337-190c8376-a45d-4d18-bdc3-6e3af0e04149.png)  
(Screenshot of add_game2.php)  

Users can see the games in their shelf by going to "View Shelf" under "My Shelf".  
![image](https://user-images.githubusercontent.com/83597131/174167794-0bdf31ec-f74a-49b8-8423-546f8649da01.png)  
(view_shelf.php)  

The Game Library is where users can look at games belonging to all users.  
![image](https://user-images.githubusercontent.com/83597131/174168477-21354ea2-7718-428b-b5d2-ae4e8de48753.png)  
(game_library.php)  

Search Games allows users to find specific games. First, users can choose a collection to search: their personal shelf, the game library, or a shelf of a friend. Next, they have the choice to input search criteria: game mechanism, keyword, creator, complexity, player count, length. Finally, they have the choice to choose how they wish results to be displayed: alphabetically, by popularity, by complexity, or by playing time.  
![image](https://user-images.githubusercontent.com/83597131/174169622-80140fa6-c9ef-4b6e-a571-d7076c7d6c5e.png)  
(search_game.php)  

The results of the search are displayed above the input form.  
![image](https://user-images.githubusercontent.com/83597131/174169733-0a74af48-c38d-46c1-ad15-a98f69014b84.png)  
(search_game.php)  

In View Shelf, Game Library, and Search Games, if you click on one of the games in the display you will be brought to a page containing specific information about the chosen game. This page also allows users to add or remove the game from their shelf.
![image](https://user-images.githubusercontent.com/83597131/174166342-343d4fd1-c8cc-4a44-ba88-2eb357aafd78.png)  
(view_game.php)  

In View Game, there is also an edit button which leads you editing page where you can change any piece of game information (except the name of the game). 
![image](https://user-images.githubusercontent.com/83597131/174170547-2f703171-db82-4072-a706-6bfc80836e80.png)  
(edit_game.php)  

In the "Add Friend" section under the "Friends" header, you can add a friend by username or click on a profile in the user list to visit the profile page of a player. 
![image](https://user-images.githubusercontent.com/83597131/174190512-2922069e-5cae-4ce2-96ae-a7c41a50ea89.png)  
![image](https://user-images.githubusercontent.com/83597131/174190541-cfcb89d3-8512-4ed1-b4ba-ba5caa4159f4.png)  
(search_game.php)  

When you visit the profile page of a player, you see their user information. If the logged in user is friends with the other user, there is also the option to view that user's game collection. If the user is not a friend, they can be sent a friend request. 
![image](https://user-images.githubusercontent.com/83597131/174190889-91d37eb3-687a-424c-b85f-ef7729adaafc.png)  
(view_player.php)

If there is a friend request made, a notification will appear on the account of the user.  
![image](https://user-images.githubusercontent.com/83597131/174190970-05662bb9-5f18-4b55-9eea-0d5188ba1b61.png)  
(add_friend.php)  

Friend requests can be accepted or declined on the user's profile page.  
![image](https://user-images.githubusercontent.com/83597131/174191232-6941246f-00e2-41e9-b062-3b69b9ec1225.png)  
(view_player.php)

You can view your own profile in "My Profile" under the section "My Account". This page shows the number of games of your shelf, your email address, your bio, and your friends.  
![image](https://user-images.githubusercontent.com/83597131/174191737-ee9ae157-4323-4a39-8994-fecaca11d716.png)  
(profile.php)

You can edit your username, email address, bio, or profile picture by clicking on the "edit" button in your profile.  
![image](https://user-images.githubusercontent.com/83597131/174191915-a45fae0d-8e6d-440e-904c-f71a5440637a.png)  
(edit_profile.php)  

## Credits  
Site Developer: Fiona Cheng  
Beta Tester: Wendy Yee  
Last edit: June 16, 2022  
