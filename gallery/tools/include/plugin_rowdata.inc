	    <td class="plugin_<?php print $modifier ?>"><?php print $plugin ?></td>
	    <td bgcolor="<?php print percentColor($pluginData['percentDone']) ?>"><?php print $pluginData['percentDone'] ?>%</td>

	    <?php if (isset($pluginData['missing'])) { ?>
	    <td colspan="5" class="missing_<?php print $modifier ?>">Missing</td>
	    <?php } else { ?>
	    <td class="total_<?php print $modifier ?>"><?php print $pluginData['total'] ?></td>
	    <td class="translated_<?php print $modifier ?>"><?php print $pluginData['translated'] ?></td>
	    <td class="fuzzy_<?php print $modifier ?>"><?php print $pluginData['fuzzy'] ?></td>
	    <td class="untranslated_<?php print $modifier ?>"><?php print $pluginData['untranslated'] ?></td>
	    <td class="obsolete_<?php print $modifier ?>"><?php print $pluginData['obsolete'] ?></td>
	    <?php } ?>
