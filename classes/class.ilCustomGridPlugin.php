<?php
include_once("./Services/Calendar/classes/class.ilAppointmentCustomGridPlugin.php");

/***
 * Plugin example for the calendar revision.
 * Plugin slot: AppointmentCustomGrid
 * https://www.ilias.de/docu/goto.php?target=wiki_1357_Plugin_Slot_for_Appointements_in_Main_Column_Grid_Calendar
 * @author Jesús López Reyes <lopez@leifos.com>
 * @version $Id$
 */
class ilCustomGridPlugin extends ilAppointmentCustomGridPlugin
{
	/**
	 * @return	string	Plugin Name
	 */
	final function getPluginName()
	{
		return "CustomGrid";
	}

	/**
	 * Replace the whole appointment presentation in the grid.
	 * @param string $a_content html grid content
	 * @return mixed string or empty.
	 */
	public function replaceContent($a_content)
	{
		//The following code is only to test the plugin and it does not follow the best practices.
		$content = $a_content;

		if($_GET['cmdClass'] == "ilcalendardaygui")
		{
			$content = str_replace('btn-link','btn btn-info',$content);
			return $content;
		}

		if($this->appointment->isFullday()) {
			$content = preg_replace('/background-color:(.*);/i','background-color:red;',$content,1);
		}
		else {
			$content = preg_replace('/background-color:(.*);/i','background-color:orange;',$content,1);
		}

		$appointment = $this->getAppointment();
		//example dealing with categories and types.
		$cat_id = ilCalendarCategoryAssignments::_lookupCategory($appointment->getEntryId());
		$cat = ilCalendarCategory::getInstanceByCategoryId($cat_id);
		$cat_info["type"] = $cat->getType();
		$cat_info["obj_id"] = $cat->getObjId();

		$str_full_day = "";
		if($this->appointment->isFullday())
		{
			$str_full_day = " <span style='color:green'>[Full day event]</span>";
		}

		if($cat_info['type'] == ilCalendarCategory::TYPE_OBJ) {
			$obj_type = ilObject::_lookupType($cat_info["obj_id"]);
			switch ($obj_type) {
				case "crs":
					$content = $content."<p style='color:magenta;background-color:yellow'>Course event".$str_full_day."</p>";
					break;
				case "sess":
					$content = "<p style='color:red;background-color:orange'>Session event".$str_full_day."</p>";
					break;
				default:
					$content = "<p style='color:deeppink;background-color:white'>No course/session event".$str_full_day."</p>";
			}
		}

		return $content;
	}

	/**
	 * @return string or empty.
	 */
	public function addExtraContent()
	{
		return "<p style='font-size:90%;'>[PLUGIN] Content added.</p>";
	}

	//this method should return only the path.
	public function addGlyph()
	{
		$appointment = $this->getAppointment();
		$icon = false;

		//example dealing with categories and types.
		$cat_id = ilCalendarCategoryAssignments::_lookupCategory($appointment->getEntryId());
		$cat = ilCalendarCategory::getInstanceByCategoryId($cat_id);
		$cat_info["type"] = $cat->getType();
		$cat_info["obj_id"] = $cat->getObjId();

		if($cat_info['type'] == ilCalendarCategory::TYPE_OBJ)
		{
			$obj_type = ilObject::_lookupType($cat_info["obj_id"]);
			switch ($obj_type)
			{
				case "crs":
					$icon = ilUtil::getImagePath("icon_crss.svg");
					break;
				case "grp":
					$icon = ilUtil::getImagePath("icon_grps.svg");
					break;
				case "sess":
					$icon = ilUtil::getImagePath("icon_sess.svg");
					break;
			}
		}
		if($icon)
		{
			return "<img src=$icon border='0'>";
		}
		else
		{
			return;
		}

	}

	//Agenda Methods
	/**
	 * @param \ILIAS\UI\Component\Item\Item $a_item
	 * @return \ILIAS\UI\Component\Item\Item
	 */
	public function editAgendaItem(\ILIAS\UI\Component\Item\Item $a_item)
	{
		global $DIC;

		$f = $DIC->ui()->factory();

		$df = new \ILIAS\Data\Factory();

		$properties = $a_item->getProperties();

		//example dealing with calendar types.
		$cat_id = ilCalendarCategoryAssignments::_lookupCategory($this->appointment->getEntryId());
		$cat = ilCalendarCategory::getInstanceByCategoryId($cat_id);

		if(ilObject::_lookupType($cat->getObjId()) == "sess") {
			$description_color = "blue";

		}
		else {
			$description_color = "orange";
		}

		//new description and properties
		if($this->appointment->isFullday()) {
			$description = "<span style='color:".$description_color."'>[Edit by Plugin - Full day event] - ".$a_item->getDescription()."</span>";
			$properties["metadata by PLUGIN"] = "<h3>This text exists because the plugin knows that this is a full day event</h3>";
		} else {
			$description = "<span style='color:".$description_color."'>[Edit by Plugin - NOT Full day event] - ".$a_item->getDescription()."</span>";
		}

		//new item color
		$new_color = '#78B44D';

		$li = $f->item()->standard($a_item->getTitle())
			->withDescription("".$description)
			->withLeadText(ilDatePresentation::formatPeriod($this->appointment->getStart(), $this->appointment->getEnd(), true))
			->withProperties($properties)
			->withColor($df->color($new_color));

		return $li;
	}

	/**
	 * @return string new shy button title.
	 */
	public function editShyButtonTitle()
	{
		return "[PLUGIN]changed this title.";
	}

}