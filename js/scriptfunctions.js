function fillform($elvalue1,$treas1,$yr){
    var elid=['gpname','coord1','coord2','bacc','inbal','inf','ind','inbi','ino','outv','oute','equip','outo'];
    var elvalue=$elvalue1;
    var treasurer=$treas1;
    var year=$yr;
    var id,yel,el;
    yel=document.getElementById('year');
    yel.innerHTML=year;
    el=document.getElementById('treas');
    el.innerHTML=treasurer;

    for (i=0;i<3;i++){
        id=elid[i];
        el=document.getElementById(id);
        el.innerHTML=elvalue[i];
    }
    for (i=3;i<4;i++){
        id=elid[i];
        form=document.getElementById('myform');
           if(elvalue[i]=='Yes'){
            form.bacc.value='Yes';
        }
        else{
            form.bacc.value='No';
        }
    }
    for (i=4;i<13;i++){
        id=elid[i];
        el=document.getElementById(id);
        el.value=parseFloat(elvalue[i]).toFixed(2);
    }
    elarray=updatetotals();
    makeTotals(elarray);
}

function updatetotals(){
    var inarray=['inf','ind','inbi','ino','outv','oute','equip','outo'];
	var elarray=[];
	for (i=0;i<8;i++){
	 elarray.push(document.getElementById(inarray[i]));
	   document.getElementById(inarray[i]).addEventListener('keypress', function(event) {
	    if (event.key === 'Enter') {
        		makeTotals(elarray);
        		}
    }
	);
	document.getElementById(inarray[i]).addEventListener('focusout', function(event) {
	       		makeTotals(elarray);
    }
	);
	}
	var outbalance=document.getElementById('outbal');
	 outbalance.addEventListener('keypress', function(event) {
	    if (event.key === 'Enter') {
        		checkBalance(outbalance);
        		}
    });
    return elarray;
}
function checkBalance(outbalance){
    var diff=document.getElementById('rlpv');
    var diff1=document.getElementById('rlp');
    var difval=diff1.value;
    difval=parseFloat(difval);
    var balenter=parseFloat(outbalance.value).toFixed(2);
    var balcalc=parseFloat(parseFloat(document.getElementById('inbal').value)+difval).toFixed(2);
    if(balcalc==balenter){
        document.getElementById('sub').click();
    }
    else{
        wrongbalancealert(); 
    }
}

function wrongbalancealert(){
    alert('The balance you have entered does not match the other figures you have entered, please check and correct errors.');

}
function makeTotals(elarray){
    var income=document.getElementById('tr');
    var payment=document.getElementById('tp');
    var diff=document.getElementById('rlpv');
    var diff1=document.getElementById('rlp');
    var rec=0;
    var pay=0;
    var reclesspay=0;
    for(i=0;i<4;i++){
        rec=rec+parseFloat(elarray[i].value);
    }
    rec=parseFloat(rec).toFixed(2);
    for(i=4;i<8;i++){
        pay=pay+parseFloat(elarray[i].value);
    }
    pay=parseFloat(pay).toFixed(2);
    income.innerHTML='£'+rec;
    payment.innerHTML='£'+pay;
    var difval=parseFloat(rec-pay).toFixed(2);
    diff.innerHTML='£'+difval;
    diff1.value=difval;
}
