function toggleKeyPass() {
    const public = document.querySelector("#choice-public"); 
    const key = document.querySelector("#host-key"); 
    const pass = document.querySelector("#host-pass"); 
    const msg = document.querySelector(".radio-msg"); 

    if (public.checked) {
        key.style.cssText = `pointer-events: none; background-color: var(--blue3);`; 
        pass.style.cssText = `pointer-events: none; background-color: var(--blue3);`; 
        msg.style.cssText = `border: 3px solid var(--yellow1); padding: 2%;`; 
    } else {
        key.style.cssText = `pointer-events: auto; background-color: white;`; 
        pass.style.cssText = `pointer-events: auto; background-color: white;`; 
        msg.style.border = "none"; 
    }
}