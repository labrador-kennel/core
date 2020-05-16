---
title: "Plugins: Booting Up"
order: 5
---
Sometimes your Plugin might need to do something one time during the Plugin loading process. This is where the 
`BootablePlugin` interface comes into play. Implementing this interface ensures that your Plugin has an opportunity to 
complete its task after all other Plugin loading procedures have finished.

### Implementing `BootablePlugin`

