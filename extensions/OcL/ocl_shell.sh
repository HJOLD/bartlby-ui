#!/bin/sh
BARTLBY_HTDOCS="/var/www/htdocs/bartlby.krone.at/";


cd $BARTLBY_HTDOCS/extensions/


echo -ne "Username:"
read I_USER;
echo -ne "Password:"
read I_PW;
echo -ne "Date (default: $(date +"%d.%m.%Y %H:%M")):"
read I_DATE;
if [ "x$I_DATE" = "x" ];
then
	I_DATE=$(date +"%d.%m.%Y %H:%M");
fi;

echo -ne "Subject:"
read I_SUBJ;

echo -ne "Duration (in minutes):"
read I_DURA;
echo -ne "Caller:"
read I_CALLER;



echo -ne "Type (sms, phone, default:sms):"
read I_TYPE;
if [ "x$I_TYPE" = "x" ];
then
	I_TYPE="sms";
fi;

echo -e "Error Message (CTRL+D when finished)"
I_ERROR_LONG="$(</dev/stdin)"




echo "----------------------------------------";
php automated.php username=$I_USER password=$I_PW script=OcL/save.php ocl_date="$I_DATE" ocl_subject="$I_SUBJ" ocl_duration="$I_DURA" ocl_caller="$I_CALLER" ocl_error_long="$I_ERROR_LONG" ocl_service_var= ocl_type="$I_TYPE" 2>&1>/dev/null;