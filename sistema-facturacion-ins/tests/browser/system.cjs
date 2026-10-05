const { chromium } = require(process.env.INS_PLAYWRIGHT);
const fs = require('node:fs');
(async()=>{
 const browser=await chromium.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true});
 const page=await browser.newPage({viewport:{width:1440,height:1000}});
 const errors=[];page.on('pageerror',e=>errors.push(e.message));
 await page.goto('http://127.0.0.1:8000/login');
 await page.getByLabel('Correo',{exact:true}).fill('admin@ins.local');
 await page.getByLabel('Contraseña',{exact:true}).fill(process.env.INS_TEST_PASSWORD);
 await Promise.all([page.waitForURL('http://127.0.0.1:8000/'),page.getByRole('button',{name:'Iniciar sesión'}).click()]);
 const out='../../output/system-review';fs.mkdirSync(out,{recursive:true});
 for(const [name,path]of [['panel','/'],['users','/users'],['business','/business'],['inventory','/inventory'],['reports','/reports'],['sale','/sales/create']]){
  const response=await page.goto('http://127.0.0.1:8000'+path);
  if(response.status()!==200)throw Error(name+': '+response.status());
  await page.screenshot({path:out+'/'+name+'.png',fullPage:true});
 }
 await page.locator('.product-select').first().selectOption({index:1});
 await page.locator('.quantity').first().fill('2');
 const total=await page.locator('#total').innerText();if(total==='$0.00')throw Error('No se actualiza el total');
 await page.setViewportSize({width:390,height:844});
 await page.goto('http://127.0.0.1:8000/');
 if(await page.evaluate(()=>document.documentElement.scrollWidth>window.innerWidth))throw Error('Desbordamiento en pantalla móvil');
 await page.screenshot({path:out+'/mobile.png',fullPage:true});
 await page.goto('http://127.0.0.1:8000/users');
 await page.getByRole('link',{name:'Editar',exact:true}).filter({visible:true}).first().click();
 const passwordField=page.locator('input[name="password"]');if(await passwordField.inputValue())throw Error('Password prefilled');
 await page.goto('http://127.0.0.1:8000/');
 await page.getByRole('button',{name:'Cerrar sesión'}).click();await page.waitForURL('**/login');
 if(errors.length)throw Error(errors.join('\n'));
 console.log(JSON.stringify({screens:7,dynamicTotal:total,errors}));await browser.close();
})().catch(e=>{console.error(e);process.exitCode=1;});
