/**
 * Gallery SVN ID:
 * $Id: multiInput.js.php 13850 2006-06-19 12:37:37Z jenst $
*/

// Password strength meter
// Written by firas kassem [2007.04.05]
// Firas Kassem  phiras.wordpress.com || phiras at gmail {dot} com
// For more information : http://phiras.wordpress.com/2007/04/08/password-strength-meter-a-jquery-plugin/

var shortPass	= 'Too short'
var badPass	= 'Bad'
var goodPass	= 'Good'
var strongPass	= 'Strong'


function checkPw(pwdfield_id) {
	password = document.getElementById(pwdfield_id).value;
	if(password.length > 0) {
		document.getElementById('result_'  +pwdfield_id).style.display = 'inline';
	}
	else {
		document.getElementById('result_'  +pwdfield_id).style.display = 'none';
	}
	
	strength = passwordStrength(password, '');
	
	// Make it more green ;-) when its not bad.
	color = (strength[0] > 30) ? strength[0] + 15: strength[0];
	
	hsvcolor={0:color,1:100,2:100};
	//alert(value);
	hexcolor = hsv2hex(hsvcolor);
	//alert(color);
	document.getElementById('result_' + pwdfield_id).value = strength[1] + ' (' + strength[0] + '%)';
	document.getElementById('result_' + pwdfield_id).style.backgroundColor = '#' + hexcolor;
}

function passwordStrength(password,username) {
    score = 0 ;
    
    //password < 4
    if (password.length < 4 ) return ([score, shortPass])
    
    //password == username
    if (password.toLowerCase()==username.toLowerCase()) return ([score,badPass])
    
    //password length
    score += password.length * 4
    score += ( checkRepetition(1,password).length - password.length ) * 1
    score += ( checkRepetition(2,password).length - password.length ) * 1
    score += ( checkRepetition(3,password).length - password.length ) * 1
    score += ( checkRepetition(4,password).length - password.length ) * 1

    //password has 3 numbers
    if (password.match(/(.*[0-9].*[0-9].*[0-9])/))  score += 5 
    
    //password has 2 symbols
    if (password.match(/(.*[!,@,#,$,%,^,&,*,?,_,~].*[!,@,#,$,%,^,&,*,?,_,~])/)) score += 5 
    
    //password has Upper and Lower chars
    if (password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/))  score += 10 
    
    //password has number and chars
    if (password.match(/([a-zA-Z])/) && password.match(/([0-9])/))  score += 15 
    //
    //password has number and symbol
    if (password.match(/([!,@,#,$,%,^,&,*,?,_,~])/) && password.match(/([0-9])/))  score += 15 
    
    //password has char and symbol
    if (password.match(/([!,@,#,$,%,^,&,*,?,_,~])/) && password.match(/([a-zA-Z])/))  score += 15 
    
    //password is just a nubers or chars
    if (password.match(/^\w+$/) || password.match(/^\d+$/) )  score -= 10 
    
    //verifing 0 < score < 100
    if ( score < 0 )  score = 0 
    if ( score > 100 )  score = 100 
    
    if (score < 34 )  return ([score, badPass])
    if (score < 68 )  return ([score, goodPass])
    
    return ([score, strongPass])
}

// checkRepetition(1,'aaaaaaabcbc')   = 'abcbc'
// checkRepetition(2,'aaaaaaabcbc')   = 'aabc'
// checkRepetition(2,'aaaaaaabcdbcd') = 'aabcd'
function checkRepetition(pLen,str) {
    res = ""
    for ( i=0; i<str.length ; i++ ) {
        repeated=true
        for (j=0;j < pLen && (j+i+pLen) < str.length;j++)
            repeated=repeated && (str.charAt(j+i)==str.charAt(j+i+pLen))
        if (j<pLen) repeated=false
        if (repeated) {
            i+=pLen-1
            repeated=false
        }
        else {
            res+=str.charAt(i)
        }
    }
    return res
}
