# Informations

- Default accounts: :
  - `admin1@mail.com` -> `admin`
  - `staff1@mail.com` -> `staff`
  - `user1@mail.com` -> `user`
  - Passwords are weak for simplicity of testing purposes.
  - All new accounts created via the registration form follows a stricter password policy.
- Images and videos path are relative to the root of the server, so you will perhaps need to change the path.
  - Line 28 of /NRV/src/classes/renderer/ImageFormRenderer.php
  - Lines 41, 43, 101, 120 of /NRV/src/classes/renderer/SpectacleRenderer.php