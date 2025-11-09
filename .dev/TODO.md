**Medie borttagning**
1. Ta bort alla `invoice`s där `loan_id` är ett lån med `copy_id` tillen kopia som har `media_id` samma som mediet
2. Ta bort alla lån med `copy_id` till en kopia som har `media_id` samma som mediet
3. Ta bort alla kopior med `media_id` samma som mediet
4. Ta bort mediet

**Ta bort kopia**
1. Ta bort alla `invoice`s där `loan_id` är ett lån med `copy_id`samma som kopian
2. Ta bort alla lån med `copy_id` samma som kopian
3. Ta bort kopian

**Ta bort användare**
1. Ta bort alla `invoice`s där `loan_id` är ett lån med `user_id` samma som användaren
2. Ta bort alla lån  lån med `user_id` samma som användaren
3. Ta bort användaren

**Ta bort ett lån**
1. Ta bort alla `invoice`s där `loan_id` är samma som lånet
2. Ta bort lånet