# HarpIA Ajax

HarpIA Ajax is a Moodle plugin that implements
an interaction with an arbitrary answer provider,
such as a language model. Its functionality can be used by 
other Moodle plugins. 

The calls to the answer providers are
performed on the server: the plugin sends the requests
to a (possibly local) instance of
[HarpIA Model Gateway](https://github.com/C4AI/HarpIA_Model_Gateway).

This plugin has been originally developed as part of
[HarpIA Survey](https://github.com/C4AI/HarpIA_Survey/),
an early-stage yet operational language model evaluation framework
based on Moodle.

### Dependencies 

- [Moodle](https://moodle.org/) (tested: version 4.5);
- [HarpIA Model Gateway](https://github.com/C4AI/HarpIA_Model_Gateway).

### Plugins that use this plugin

HarpIA Ajax serves no purpose without other plugins that call it. 

Currently, only the following plugin uses HarpIA Ajax:

- [HarpIA Interaction](https://github.com/C4AI/moodle-datafield_harpiainteraction) plugin.
