const messagesBox=document.getElementById("messagesBox"), negotiationTitle=document.getElementById("negotiationTitle"), negotiationStatus=document.getElementById("negotiationStatus"), negotiationActions=document.getElementById("negotiationActions"), messageForm=document.getElementById("messageForm"), messageInput=document.getElementById("messageInput"), priceInput=document.getElementById("priceInput"), messageAlert=document.getElementById("messageAlert");
const negotiationId = new URLSearchParams(window.location.search).get("id");
let currentUserId=null;

function showAlert(t,type){messageAlert.textContent=t; messageAlert.className="alert "+type;}

async function loadNegotiation(){
  try{
    const response=await fetch(`${API_BASE_URL}/get_negotiation.php?id=${negotiationId}`,{method:"GET",credentials:"include"});
    const result=await response.json();
    if(response.status===401){window.location.href="connexion.html";return;}
    if(!result.success){messagesBox.innerHTML=`<p>${result.message||"Erreur"}</p>`;return;}
    currentUserId=Number(result.current_user_id);
    displayNegotiation(result.negotiation,result.messages);
  }catch(e){messagesBox.innerHTML="<p>Erreur de connexion.</p>";}
}

function displayNegotiation(neg,messages){
  negotiationTitle.textContent=`${neg.brand} ${neg.model} - ${neg.title}`;
  negotiationStatus.textContent=formatStatus(neg.status);
  messagesBox.innerHTML = messages.length ? messages.map(msg=>{
    const mine=Number(msg.sender_id)===currentUserId;
    const price=msg.proposed_price?`<strong>Proposition : ${Number(msg.proposed_price).toLocaleString("fr-FR")} €</strong>`:"";
    return `<div class="message-bubble ${mine?"mine":"other"}"><div class="message-author">${msg.first_name} ${msg.last_name}</div><p>${msg.message}</p>${price}<small>${new Date(msg.created_at).toLocaleString("fr-FR")}</small></div>`;
  }).join("") : "<p>Aucun message.</p>";

  if(neg.status!=="open") messageForm.style.display="none";
  const isSeller=Number(neg.seller_id)===currentUserId;
  negotiationActions.innerHTML = isSeller && neg.status==="open" ? `<button onclick="updateNegotiationStatus('accepted')" class="primary-btn">Accepter</button><button onclick="updateNegotiationStatus('rejected')" class="secondary-btn">Refuser</button>` : "";
}

messageForm.addEventListener("submit",async e=>{
  e.preventDefault();
  try{
    const response=await fetch(`${API_BASE_URL}/send_negotiation_message.php`,{method:"POST",credentials:"include",headers:{"Content-Type":"application/json"},body:JSON.stringify({negotiation_id:negotiationId,message:messageInput.value,proposed_price:priceInput.value})});
    const result=await response.json();
    if(!result.success){showAlert(result.message||"Erreur","error");return;}
    messageInput.value=""; priceInput.value=""; showAlert("Message envoyé.","success"); await loadNegotiation();
  }catch(e){showAlert("Erreur de connexion.","error");}
});

async function updateNegotiationStatus(status){
  if(!confirm(status==="accepted"?"Accepter cette négociation ? La voiture sera marquée comme vendue.":"Refuser cette négociation ?")) return;
  try{
    const response=await fetch(`${API_BASE_URL}/update_negotiation_status.php`,{method:"POST",credentials:"include",headers:{"Content-Type":"application/json"},body:JSON.stringify({negotiation_id:negotiationId,status})});
    const result=await response.json();
    if(!result.success){showAlert(result.message||"Erreur","error");return;}
    showAlert("Négociation mise à jour.","success"); await loadNegotiation();
  }catch(e){showAlert("Erreur de connexion.","error");}
}

function formatStatus(s){return s==="open"?"Ouverte":s==="accepted"?"Acceptée":s==="rejected"?"Refusée":s==="closed"?"Fermée":s;}
loadNegotiation();
