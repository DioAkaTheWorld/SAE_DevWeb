# Informations

- Default accounts: :
  - `admin1@mail.com` -> `admin1`
  - `staff1@mail.com` -> `staff1`
  - `user1@mail.com` -> `user1`
  - Passwords are weak for simplicity of testing purposes.
  - All new accounts created via the registration form follows a stricter password policy.
- Images and videos path are relative to the root of the server, so you will perhaps need to change the path. (e.g relative to htdocs with xampp)
  - Line 28 of /NRV/src/classes/renderer/ImageFormRenderer.php
  - Lines 41, 43, 101, 120 of /NRV/src/classes/renderer/SpectacleRenderer.php
- You have to create a folder named "videos" in /media/ to store the videos.