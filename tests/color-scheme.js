// node tests/color-scheme.js: exercise preferences without browser storage dependencies.
const assert = require('node:assert/strict');
const vm = require('node:vm');
const fs = require('node:fs');
const source = fs.readFileSync('theme/assets/js/color-scheme.js', 'utf8');
function setup(saved, dark, blocked = false) {
 const events = {}, buttons = [0, 1].map(() => ({ hidden: true, setAttribute(k,v) { this[k]=v; }, addEventListener(k,v) { this[k]=v; } }));
 const root = {dataset:{}};
 const system = {matches:dark, addEventListener(k,v) { this.change=v; }};
 const storage = {getItem() { if(blocked) throw Error(); return saved; }, setItem(k,v) { if(blocked) throw Error(); saved=v; }};
 vm.runInNewContext(source, {document:{documentElement:root,querySelectorAll:()=>buttons,addEventListener:(k,v)=>events[k]=v}, window:{matchMedia:()=>system,addEventListener:(k,v)=>events[k]=v},localStorage:storage});
 assert.equal(root.dataset.colorScheme, saved === 'light' ? 'light' : dark || saved === 'dark' ? 'dark':'light');
 events.DOMContentLoaded();
 return {root,system,events,buttons};
}
for (const blocked of [false,true]) {
 const t=setup(null,true,blocked);
 assert.equal(t.buttons[0]['aria-checked'],'true');
 t.buttons[0].click();
 assert.equal(t.root.dataset.colorScheme,'light');
 assert.ok(t.buttons.every(b=>b['aria-checked']==='false' && !b.hidden));
 t.system.matches=true; t.system.change();
 assert.equal(t.root.dataset.colorScheme,'light');
 t.events.storage({key:'koji-d3-color-scheme',newValue:'dark'});
 assert.ok(t.buttons.every(b=>b['aria-checked']==='true'));
 t.system.matches=false; t.events.storage({key:null,newValue:null});
 assert.equal(t.root.dataset.colorScheme,'light');
}
setup('light',true); setup('dark',false); setup('invalid',false);
console.log('Color scheme preference, synchronization and storage-failure checks passed.');
